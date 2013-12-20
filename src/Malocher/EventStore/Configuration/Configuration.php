<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Configuration;

use Malocher\EventStore\Adapter\AdapterInterface;
use Malocher\EventStore\EventStore;
use Malocher\EventStore\EventSourcing\EventSourcedObjectFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Malocher\EventStore\CqrsBridge\PublishEventsListener;
/**
 * Configuration
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Configuration
{
    protected $config = array();
    
    protected $adapter;
    
    protected $objectFactory;
    
    protected $eventDispatcher;


    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            $this->config = $config;
        }
    }
    
    /**
     * 
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        if (is_null($this->adapter)) {
            $adapterConfig = $this->config['adapter'];
            foreach ($adapterConfig as $adapterClass => $adapterConfig) {
                $this->adapter = new $adapterClass($adapterConfig);
            }
        } 
        
        return $this->adapter;
    }
    
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }
    
    /**
     * Get snapshot lookup flag
     * 
     * @return boolean
     */
    public function isSnapshotLookup()
    {
        return (isset($this->config['snapshot_lookup']))? (bool)$this->config['snapshot_lookup'] : false;
    }
    
    /**
     * Get auto generate snapshots flag
     * 
     * @return boolean
     */
    public function isAutoGenerateSnapshots()
    {
        return (isset($this->config['auto_generate_snapshots']))? (bool)$this->config['auto_generate_snapshots'] : false;
    }
    
    /**
     * 
     * @return int
     */
    public function getSnapshotInterval()
    {
        if (isset($this->config['snapshot_interval'])) {
            return (int)$this->config['snapshot_interval'];
        }
        
        return 100;
    }
    
    /**
     * Get map of $sourceFQCNs to $repositoryFQCNs
     * 
     * @return array
     */
    public function getRepositoryMap()
    {
        return (isset($this->config['repository_map']))? $this->config['repository_map'] : array();
    }
    
    /**
     * 
     * @return EventSourcedObjectFactory
     */
    public function getObjectFactory()
    {
        if (is_null($this->objectFactory)) 
        {
            if (isset($this->config['object_factory'])) {
                $objectFactoryConfig = $this->config['object_factory'];

                foreach ($objectFactoryConfig as $objectFactoryClass => $config) {
                    $this->objectFactory = new $objectFactoryClass($config);
                }
            } else {
                $this->objectFactory = new EventSourcedObjectFactory();
            }
        }
        
        return $this->objectFactory;
    }
    
    public function setObjectFactory(EventSourcedObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }
    
    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if (is_null($this->eventDispatcher)) {
            $this->eventDispatcher = (isset($this->config['event_dispatcher']))? 
                $this->config['event_dispatcher'] : new EventDispatcher();
        }
        
        return $this->eventDispatcher;
    }
    
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
        
    public function addListeners(EventStore $eventStore)
    {
        $this->tryAddCqrsListener($eventStore);
        //@odo: implement listener registration via configuration
    }
    
    protected function tryAddCqrsListener(EventStore $eventStore)
    {
        if (isset($this->config['enable_cqrs']) && $this->config['enable_cqrs']) {
            $publishEventListener = new PublishEventsListener($this->config['cqrs_bridge']);
            $eventStore->events()->addSubscriber($publishEventListener);
        }
    }
}
