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
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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


    /**
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        if (is_array($config)) {            
            $this->config = $config;
            
            if (isset($config['repository_map'])) {
                //Set map again to trigger validation
                $this->setRepositoryMap($config['repository_map']);
            }
        }
    }
    
    /**
     * @return AdapterInterface
     * @throws ConfigurationException
     */
    public function getAdapter()
    {
        if (is_null($this->adapter)) {
            if (!isset($this->config['adapter'])) {
                throw ConfigurationException::configurationError('Missing key adapter');
            }
            
            if (!is_array($this->config['adapter'])) {
                throw ConfigurationException::configurationError('Adapter configuration must be an array');
            }
            
            $adapterClass = key($this->config['adapter']);
            $adapterConfig = current($this->config['adapter']);
            
            if (!is_string($adapterClass)) {
                throw ConfigurationException::configurationError('AdapterClass must be a string');
            }
            
            if (!class_exists($adapterClass)) {
                throw ConfigurationException::configurationError(sprintf(
                    'Unknown AdapterClass: %s',
                    $adapterClass
                ));
            }
            
            $this->adapter = new $adapterClass($adapterConfig); 
            
            if (!$this->adapter instanceof AdapterInterface) {
                throw ConfigurationException::configurationError('EventStore Adapter must be instance of Malocher\EventStore\Adapter\AdapterInterface');
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
     * Get map of $sourceFQCNs to $repositoryFQCNs
     * 
     * @return array
     */
    public function getRepositoryMap()
    {
        return (isset($this->config['repository_map']))? $this->config['repository_map'] : array();
    }
    
    /**
     * @param array $map
     */
    public function setRepositoryMap(array $map)
    {
        foreach ($map as $sourceFQCN => $repositoryFQCN) {
            $this->addRepositoryMapping($sourceFQCN, $repositoryFQCN);
        }
    }
    
    /**
     * @param string $sourceFQCN
     * @param string $repositoryFQCN
     * @throws ConfigurationException
     */
    public function addRepositoryMapping($sourceFQCN, $repositoryFQCN)
    {
        if (!class_exists($sourceFQCN)) {
            throw ConfigurationException::configurationError(sprintf(
                'Unknown SourceClass: %s',
                $sourceFQCN
            ));
        }
        
        if (!class_exists($repositoryFQCN)) {
            throw ConfigurationException::configurationError(sprintf(
                'Unknown RepositoryClass: %s',
                $repositoryFQCN
            ));
        }
        
        if (!isset($this->config['repository_map']) || !is_array($this->config['repository_map'])) {
            $this->config['repository_map'] = array();
        }
        
        $this->config['repository_map'][$sourceFQCN] = $repositoryFQCN;
    }
    
    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if (is_null($this->eventDispatcher)) {
            
            if (isset($this->config['event_dispatcher'])) {
                $eventDispatcherConfig = $this->config['event_dispatcher'];

                if (!is_array($eventDispatcherConfig)) {
                    throw ConfigurationException::configurationError('EventDispatcher configuration must be an array');
                }

                list($eventDispatcherClass, $config) = each($eventDispatcherConfig);

                if (!is_string($eventDispatcherClass)) {
                    throw ConfigurationException::configurationError('EventDispatcher class must be a string');
                }

                if (!class_exists($eventDispatcherClass)) {
                    throw ConfigurationException::configurationError(sprintf(
                        'Unknown EventDispatcher class: %s',
                        $eventDispatcherClass
                    ));
                }

                if (!empty($config)) {
                    $this->eventDispatcher = new $eventDispatcherClass($config);
                } else {
                    $this->eventDispatcher = new $eventDispatcherClass();
                }
            } else {
                $this->eventDispatcher = new EventDispatcher();
            }
        }
        
        return $this->eventDispatcher;
    }
    
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
