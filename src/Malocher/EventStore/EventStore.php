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

/**
 * EventStore 
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function save($sourceType, EventSourcedInterface $eventSourcedObject)
    {
        $pendingEvents = $eventSourcedObject->getPendingEvents();
        
        if (count($pendingEvents)) {
            $this->adapter->addToStream(
                $this->getShortSourceType($sourceType), 
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
                    $this->getShortSourceType($sourceType), 
                    $eventSourcedObject->getId(), 
                    $snapshotEvent
                );
            }
        }
        
        $this->identityMap[$this->getIdentityHash($eventSourcedObject)] = $eventSourcedObject;
    }
   
    
    public function find($sourceType, $sourceId)
    {
        
    }
    
    protected function getIdentityHash(EventSourcedInterface $eventSourcedObject)
    {
        $className = get_class($eventSourcedObject);
        
        return $className . '::' . $eventSourcedObject->getId();
    }
    
    protected function getShortSourceType($FQCNSourceType)
    {
        return join('', array_slice(explode('\\', $FQCNSourceType), -1));
    }
}
