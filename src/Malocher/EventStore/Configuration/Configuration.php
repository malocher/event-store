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
use Malocher\EventStore\EventSourcing\EventSourcedObjectFactory;
/**
 *  Configuration
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Configuration
{
    protected $config = array();
    
    protected $adapter;
    
    protected $objectFactory;


    /**
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            $this->config = $config;
        }
    }

    /**
     * Check if configuration is valid
     *
     * @todo implement
     * @return bool
     */
    public function validateConfiguration()
    {
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

    /**
     * Set the active adapter
     *
     * @param AdapterInterface $adapter
     */
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
     * Map of short sourcceTypes and their corresponding FQCNs
     * 
     * @return array
     */
    public function getSourceTypeClassMap()
    {
        return (isset($this->config['source_type_class_map']))? $this->config['source_type_class_map'] : array();
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

    /**
     * Set the object factory
     *
     * @param EventSourcedObjectFactory $objectFactory
     */
    public function setObjectFactory(EventSourcedObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }
}
