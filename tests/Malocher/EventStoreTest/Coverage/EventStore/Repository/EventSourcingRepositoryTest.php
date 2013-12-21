<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Coverage\EventStore\Repository;

use Malocher\EventStore\Repository\EventSourcingRepository;
use Malocher\EventStore\EventSourcing\EventSourcedObjectFactory;
use Malocher\EventStore\EventStore;
use Malocher\EventStore\Configuration\Configuration;
use Malocher\EventStoreTest\TestCase;
/**
 * EventSourcingRepositoryTest
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventSourcingRepositoryTest extends TestCase
{
    /**
     *
     * @var EventSourcingRepository
     */
    protected $repository;


    protected function setUp()
    {
        $this->initEventStoreAdapter();
        $this->getEventStoreAdapter()->createSchema(array('User'));
        
        $eventStoreConfig = new Configuration();
        $eventStoreConfig->setAdapter($this->getEventStoreAdapter());
        $eventStore = new EventStore($eventStoreConfig);
        
        $this->repository = $eventStore->getRepository(
            'Malocher\EventStoreTest\Coverage\Mock\User'
        );
    }
    
    public function testSaveAndFind()
    {
        $factory = new EventSourcedObjectFactory();
        $user = $factory->create('Malocher\EventStoreTest\Coverage\Mock\User', '1');
        
        $user->changeName('Malocher');
        $user->changeEmail('my.email@getmalocher.org');
        
        $this->repository->save($user);
        
        $checkUser = $this->repository->find('1');
        
        $this->assertEquals($user->getName(), $checkUser->getName());
    }
}
