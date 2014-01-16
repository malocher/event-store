<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore;

use Malocher\EventStore\Adapter\AdapterInterface;
use Malocher\EventStore\Adapter\Feature\TransactionFeatureInterface;
use Malocher\EventStore\Adapter\AdapterException;
use Malocher\EventStore\Configuration\Configuration;
use Malocher\EventStore\EventSourcing\EventSourcedInterface;
use Malocher\EventStore\EventSourcing\EventSourcedObjectFactory;
use Malocher\EventStore\Repository\RepositoryInterface;
use Malocher\EventStore\StoreEvent\PostPersistEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
/**
 * EventStore 
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 * @package Malocher\EventStore
 */
class EventStore 
{
    /**
     *
     * @var AdapterInterface 
     */
    protected $adapter;    
    
    /**
     * The EventSourcedObject identity map
     * 
     * @var array
     */
    protected $identityMap = array();
    
    /**
     * Activate snapshot lookup when loading EventSourcedObjects
     * 
     * @var boolean 
     */
    protected $lookupSnapshots = false;

    /**
     * Activate the auto generation of snapshots.
     * 
     * If set to true, the lookupSnapshots property becomes true, too.
     * 
     * @var boolean 
     */
    protected $autoGenerateSnapshots = false;

    /**
     * Interval for creating snapshots of EventSourcedObjects
     * 
     * @var integer 
     */
    protected $snapshotInterval;
           
    /**
     * Map of $sourceFQCNs to $repositoryFQCNs
     * 
     * @var array 
     */
    protected $repositoryMap = array();

    /**
     *
     * @var EventSourcedObjectFactory 
     */
    protected $objectFactory;
    
    /**
     * @var EventDispatcherInterface 
     */
    protected $eventDispatcher;
    
    /**
     * @var boolean
     */
    protected $inTransaction = false;
    
    /**
     * @var array
     */
    protected $pendingEvents = array();

    /**
     * Construct
     * 
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->adapter               = $config->getAdapter();
        $this->lookupSnapshots       = $config->isSnapshotLookup();
        $this->autoGenerateSnapshots = $config->isAutoGenerateSnapshots();
        $this->snapshotInterval      = $config->getSnapshotInterval();
        $this->repositoryMap         = $config->getRepositoryMap();
        $this->objectFactory         = $config->getObjectFactory();
        $this->eventDispatcher       = $config->getEventDispatcher();
        
                
        if ($this->autoGenerateSnapshots) {
            $this->lookupSnapshots = true;
        }
    }

    /**
     * Get the active EventStoreAdapter
     * 
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
    
    /**
     * Get responsible repository for given source FQCN
     * 
     * @param string $sourceFQCN
     * 
     * @return RepositoryInterface
     */
    public function getRepository($sourceFQCN)
    {
        $hash = 'repository::' . $sourceFQCN;
        
        if (isset($this->identityMap[$hash])) {
            return $this->identityMap[$hash];
        }
        
        $repositoryFQCN = (isset($this->repositoryMap[$sourceFQCN]))?
            $this->repositoryMap[$sourceFQCN] 
            : 'Malocher\EventStore\Repository\EventSourcingRepository';
        
        $repository = new $repositoryFQCN($this, $sourceFQCN);
        
        $this->identityMap[$hash] = $repository;
        
        return $repository;
    }
    
    /**
     * Get EventDispatcher of the EventStore
     * 
     * @return EventDispatcherInterface
     */
    public function events()
    {
        return $this->eventDispatcher;
    }

    /**
     * Save given EventSourcedObject
     * 
     * @param EventSourcedInterface $eventSourcedObject
     * 
     * @return void
     */
    public function save(EventSourcedInterface $eventSourcedObject)
    {
        $sourceFQCN = get_class($eventSourcedObject);
        
        $pendingEvents = $eventSourcedObject->getPendingEvents();
        
        if (count($pendingEvents)) {
            $this->adapter->addToStream(
                $sourceFQCN, 
                $eventSourcedObject->getId(), 
                $pendingEvents
            );
            
            $postPersistEvent = new PostPersistEvent($eventSourcedObject, $pendingEvents);
            
            $lastEvent = array_pop($pendingEvents);
            
            //Check if we have to generate a snapshot 
            if ($this->autoGenerateSnapshots 
                && $this->snapshotInterval > 0 
                && $lastEvent->getSourceVersion() % $this->snapshotInterval === 0) {
                $snapshotEvent = $eventSourcedObject->getSnapshot();
                $this->adapter->createSnapshot(
                    $sourceFQCN, 
                    $eventSourcedObject->getId(), 
                    $snapshotEvent
                );
            }
            
            $this->addPendingEvent($postPersistEvent);
            $this->tryDispatchPostPersistEvents();
        }
        
        $hash = $this->getIdentityHash(
            get_class($eventSourcedObject), 
            $eventSourcedObject->getId()
        );
        
        $this->identityMap[$hash] = $eventSourcedObject;
    }
   
    /**
     * Load an EventSourcedObject by it's FQCN and id
     * 
     * @param string $sourceFQCN
     * @param string $sourceId
     * 
     * @return EventSourcedInterface|null
     */        
    public function find($sourceFQCN, $sourceId)
    {
        $hash = $this->getIdentityHash($sourceFQCN, $sourceId);
        
        if (isset($this->identityMap[$hash])) {
            return $this->identityMap[$hash];
        }
        
        $snapshotVersion = null;
        
        if ($this->lookupSnapshots) {
            $snapshotVersion = $this->adapter->getCurrentSnapshotVersion($sourceFQCN, $sourceId);
        }
        
        $historyEvents = $this->adapter->loadStream($sourceFQCN, $sourceId, $snapshotVersion);
        
        if (count($historyEvents) === 0) {
            return null;
        }
        
        return $this->objectFactory->create($sourceFQCN, $sourceId, $historyEvents);
    }
    
    /**
     * Clear cached objects
     * 
     * @return void
     */
    public function clear()
    {
        $this->identityMap = array();
    }
    
    /**
     * Begin transaction
     * 
     * @throws AdapterException If adapter does not support transactions
     */
    public function beginTransaction()
    {
        if (!$this->adapter instanceof TransactionFeatureInterface) {
            throw AdapterException::unsupportedFeatureException('TransactionFeature');
        }
        
        $this->inTransaction = true;
        $this->adapter->beginTransaction();
    }
    
    /**
     * Commit transaction
     * 
     * @throws AdapterException If adapter does not support transactions
     */
    public function commit()
    {
        if (!$this->adapter instanceof TransactionFeatureInterface) {
            throw AdapterException::unsupportedFeatureException('TransactionFeature');
        }
        
        $this->adapter->commit();
        $this->inTransaction = false;
        $this->tryDispatchPostPersistEvents();
    }
    
    /**
     * Rollback transaction
     * 
     * @throws AdapterException If adapter does not support transactions
     */
    public function rollback()
    {
        if (!$this->adapter instanceof TransactionFeatureInterface) {
            throw AdapterException::unsupportedFeatureException('TransactionFeature');
        }
        
        $this->adapter->rollback();
        $this->inTransaction = false;
        $this->pendingEvents = array();
    }
    
    /**
     * Get hash to identify EventSourcedObject in the IdentityMap
     * 
     * @param string $sourceFQCN
     * @param string $sourceId
     * 
     * @return string
     */
    protected function getIdentityHash($sourceFQCN, $sourceId)
    {        
        return $sourceFQCN . '::' . $sourceId;
    }
    
    /**
     * @param PostPersistEvent $event
     */
    protected function addPendingEvent(PostPersistEvent $event)
    {
        $this->pendingEvents[] = $event;
    }


    /**
     * Events are only dispatched if the event store has no running transaction
     */
    protected function tryDispatchPostPersistEvents()
    {
        if (!$this->inTransaction) {
            $events = $this->pendingEvents;
            $this->pendingEvents = array();
            
            foreach ($events as $event) {
                $this->events()->dispatch(PostPersistEvent::NAME, $event);
            }
        }
    }
}
