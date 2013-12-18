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
/**
 *  EventStoreConfiguration
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventStoreConfiguration
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
     * 
     * @return int
     */
    public function getSnapshotInterval()
    {
        if (isset($this->config['snapshot_interval'])) {
            return (int)$this->config['snapshot_interval'];
        }
        
        return 20;
    }
}
