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
        return $this->config['adapter'];
    }
    
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->config['adapter'] = $adapter;
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
        return (isset($this->config['source_type_class_map']))? (bool)$this->config['source_type_class_map'] : array();
    }
    
    /**
     * 
     * @return EventSourcedObjectFactory
     */
    public function getObjectFactory()
    {
        return (isset($this->config['object_factory']))? (bool)$this->config['object_factory'] : new EventSourcedObjectFactory();
    }
}
