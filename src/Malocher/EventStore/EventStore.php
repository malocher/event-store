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
use Malocher\EventStore\Configuration\Configuration;
use Malocher\EventStore\EventSourcing\EventSourcedInterface;
use Malocher\EventStore\EventSourcing\EventSourcedObjectFactory;
use Malocher\EventStore\Repository\RepositoryInterface;
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
     * A map of short sourceTypes and their corresponding FQCNs
     * 
     * @var type 
     */
    protected $sourceTypeClassMap = array();
    
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
     * Construct
     * 
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->adapter = $config->getAdapter();
        $this->lookupSnapshots = $config->isSnapshotLookup();
        $this->autoGenerateSnapshots = $config->isAutoGenerateSnapshots();
        $this->snapshotInterval = $config->getSnapshotInterval();
        $this->sourceTypeClassMap = $config->getSourceTypeClassMap();
        $this->objectFactory = $config->getObjectFactory();
                
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
     * @return EventSourcedInterface
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
}
