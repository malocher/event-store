<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Configuration;

use Malocher\EventStore\Adapter\AdapterInterface;
use Malocher\EventStore\Adapter\Doctrine\DoctrineDbalAdapter;
use Malocher\EventStore\Repository\EventSourcingRepository;
use Malocher\EventStore\EventSourcing\EventSourcedObjectFactory;
use Malocher\EventStore\Configuration\Configuration;
use Malocher\EventStoreTest\TestCase;
use Malocher\EventStoreTest\Coverage\Mock\EmptyEventSourcedObject;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 *  EventStoreConfigurationTest
 *
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStoreTest\Configuration
 */
class ConfigurationTest extends TestCase
{
    public function testGetAdapter()
    {
        $config = new Configuration(array(
            'adapter' => array(
                'Malocher\EventStore\Adapter\Doctrine\DoctrineDbalAdapter' => array(
                    'connection' => array(
                        'driver' => 'pdo_sqlite',
                        'memory' => true
                    )
                )
            )
        ));
        
        $this->assertInstanceOf('Malocher\EventStore\Adapter\Doctrine\DoctrineDbalAdapter', $config->getAdapter());
    }
    
    public function testSetAdapter()
    {
        $config = new Configuration();
        $adapter = new DoctrineDbalAdapter(array(
            'connection' => array(
                'driver' => 'pdo_sqlite',
                'memory' => true
            )
        ));
        
        $config->setAdapter($adapter);
        
        $this->assertSame($adapter, $config->getAdapter());
    }
    
    public function testDefaultIsSnapshotLookup()
    {
        $config = new Configuration();
        
        $this->assertFalse($config->isSnapshotLookup());
    }
    
    public function testIsSnapshotLookup()
    {
        $config = new Configuration(array(
            'snapshot_lookup' => true
        ));
        
        $this->assertTrue($config->isSnapshotLookup());
    }
    
    public function testDefaultIsAutoGenerateSnapshots()
    {
        $config = new Configuration();
        
        $this->assertFalse($config->isAutoGenerateSnapshots());
    }
    
    public function testIsAutoGenerateSnapshots()
    {
        $config = new Configuration(array(
            'auto_generate_snapshots' => true
        ));
        
        $this->assertTrue($config->isAutoGenerateSnapshots());
    }
    
    public function testDefaultGetSnapshotInterval()
    {
        $config = new Configuration();
        
        $this->assertEquals(100, $config->getSnapshotInterval());
    }
    
    public function testGetSnapshotInterval()
    {
        $config = new Configuration(array(
            'snapshot_interval' => 50
        ));
        
        $this->assertEquals(50, $config->getSnapshotInterval());
    }
    
    public function testAddRepositoryMapping()
    {
        $config = new Configuration();
        $config->addRepositoryMapping(
            'Malocher\EventStoreTest\Coverage\Mock\EmptyEventSourcedObject', 
            'Malocher\EventStore\Repository\EventSourcingRepository'
        );
        
        $check = array(
            'Malocher\EventStoreTest\Coverage\Mock\EmptyEventSourcedObject' => 
            'Malocher\EventStore\Repository\EventSourcingRepository'
        );
        
        $this->assertEquals($check, $config->getRepositoryMap());
    }
    
    public function testSetRepositoryMap()
    {
        $config = new Configuration();
        
        $map = array(
            'Malocher\EventStoreTest\Coverage\Mock\EmptyEventSourcedObject' => 
            'Malocher\EventStore\Repository\EventSourcingRepository'
        );
        
        $config->setRepositoryMap($map);
        
        $this->assertEquals($map, $config->getRepositoryMap());
    }
    
    public function testDefaultGetObjectFactory()
    {
        $config = new Configuration();
        
        $this->assertInstanceOf('Malocher\EventStore\EventSourcing\EventSourcedObjectFactory', $config->getObjectFactory());
    }
    
    public function testGetObjectFactory()
    {
        $config = new Configuration(array(
            'object_factory' => array(
                'Malocher\EventStoreTest\Coverage\Mock\MockedObjectFactory' => array()
            )
        ));
        
        $this->assertInstanceOf('Malocher\EventStoreTest\Coverage\Mock\MockedObjectFactory', $config->getObjectFactory());
    }
    
    public function testSetObjectFactory()
    {
        $config = new Configuration(array(
            'object_factory' => array(
                'Malocher\EventStoreTest\Coverage\Mock\MockedObjectFactory' => array()
            )
        ));
        
        $objectFactory = new EventSourcedObjectFactory();
        $config->setObjectFactory($objectFactory);
        
        //Ignore array config and return set factory
        $this->assertSame($objectFactory, $config->getObjectFactory());
    }
    
    public function testDefaultGetEventDispatcher()
    {
        $config = new Configuration();
        
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventDispatcher', $config->getEventDispatcher());
    }
    
    public function testGetEventDispatcher()
    {
        $config = new Configuration(array(
            'event_dispatcher' => array(
                'Malocher\EventStoreTest\Coverage\Mock\MockedEventDispatcher' => array()
            )
        ));
        
        $this->assertInstanceOf('Malocher\EventStoreTest\Coverage\Mock\MockedEventDispatcher', $config->getEventDispatcher());
    }
    
    public function testSetEventDispatcher()
    {
        $config = new Configuration(array(
            'event_dispatcher' => array(
                'Malocher\EventStoreTest\Coverage\Mock\MockedEventDispatcher' => array()
            )
        ));
        
        $eventDispatcher = new EventDispatcher();
        $config->setEventDispatcher($eventDispatcher);
        
        //Ignore array config and return manually set EventDispatcher
        $this->assertSame($eventDispatcher, $config->getEventDispatcher());
    }
}
