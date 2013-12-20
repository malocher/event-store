<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Coverage\EventStore;

use Malocher\EventStore\Configuration\Configuration;
use Malocher\EventStore\EventSourcing\EventSourcedObjectFactory;
use Malocher\EventStore\EventStore;
use Malocher\EventStore\StoreEvent\PostPersistEvent;
use Malocher\EventStoreTest\TestCase;

use Malocher\EventStoreTest\Coverage\Mock\User;
/**
 * EventStoreTest
 * 
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStoreTest\Coverage\EventStore
 */
class EventStoreTest extends TestCase
{
    /**
     * @var EventStore
     */
    private $eventStore;

    public function setUp()
    {
        $this->initEventStoreAdapter();
        $this->createStream('user_stream');
        
        $config = new Configuration();
        $config->setAdapter($this->getEventStoreAdapter());        
        $this->eventStore = new EventStore($config);
    }

    public function testConstructed()
    {
        $this->assertInstanceOf('Malocher\EventStore\EventStore', $this->eventStore);
    }
    
    public function testSaveAndFind()
    {
        $user = new User('1');
        
        $user->changeName('Malocher');
        $user->changeEmail('my.email@getmalocher.org');
        
        $this->eventStore->save($user);
        
        $this->eventStore->clear();
        
        $userFQCN = get_class($user);
        
        $checkUser = $this->eventStore->find($userFQCN, '1');
        
        $this->assertInstanceOf($userFQCN, $checkUser);
        $this->assertNotSame($user, $checkUser);
        $this->assertEquals($user->getName(), $checkUser->getName());
        $this->assertEquals($user->getEmail(), $checkUser->getEmail());
    }
    
    public function testIdentityMap()
    {
        $factory = new EventSourcedObjectFactory();
        $user = $factory->create('Malocher\EventStoreTest\Coverage\Mock\User', '1');
        
        $user->changeName('Malocher');
        $user->changeEmail('my.email@getmalocher.org');
        
        $this->eventStore->save($user);
        
        $userFQCN = get_class($user);
        
        $checkUser = $this->eventStore->find($userFQCN, '1');
        
        $this->assertSame($user, $checkUser);
    }
    
    public function testGetRepository()
    {
        $repo = $this->eventStore->getRepository(
            'Malocher\EventStoreTest\Coverage\Mock\User'
        );
        
        $this->assertInstanceOf(
            'Malocher\EventStore\Repository\EventSourcingRepository',
            $repo
        );
        
        $sameRepo = $this->eventStore->getRepository(
            'Malocher\EventStoreTest\Coverage\Mock\User'
        );
        
        $this->assertSame($repo, $sameRepo);
    }
    
    public function testDispatchPostPersistEvent()
    {
        $factory = new EventSourcedObjectFactory();
        $user = $factory->create('Malocher\EventStoreTest\Coverage\Mock\User', '1');
        
        $user->changeName('Malocher');
        $user->changeEmail('my.email@getmalocher.org');
        
        $persistedEventList = array();        
        
        $this->eventStore->events()->addListener(
            PostPersistEvent::NAME, 
            function(PostPersistEvent $e) use (&$persistedEventList) {
                foreach ($e->getPersistedEvents() as $persistedEvent) {
                    $persistedEventList[] = get_class($persistedEvent);
                }
            }
        );
        
        $this->eventStore->save($user);
        
        $check = array(
            'Malocher\EventStoreTest\Coverage\Mock\Event\UserNameChangedEvent',
            'Malocher\EventStoreTest\Coverage\Mock\Event\UserEmailChangedEvent'
        );
        
        $this->assertEquals($check, $persistedEventList);
    }
}
