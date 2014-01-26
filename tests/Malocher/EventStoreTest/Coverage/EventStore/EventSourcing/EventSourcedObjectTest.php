<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Coverage\EventStore\EventSourcing;

use Malocher\EventStoreTest\Coverage\Mock\EmptyEventSourcedObject;
use Malocher\EventStoreTest\Coverage\Mock\User;
use Malocher\EventStoreTest\Coverage\Mock\Event\UserNameChangedEvent;
use Malocher\EventStoreTest\Coverage\Mock\Event\UserEmailChangedEvent;
use Malocher\EventStore\EventSourcing\ProtectedAccessDecorator;
use Malocher\EventStoreTest\TestCase;

/**
 * AbstractEventSourcedTest
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventSourcedObjectTest extends TestCase
{
    /**
     *
     * @var User
     */
    protected $eventSourcedObject;
    
    protected function setUp() 
    {
        $this->eventSourcedObject = new User('1');
    }

    public function testGetId()
    {
        $this->assertEquals( '1', $this->eventSourcedObject->getId() );
    }

    public function testGetPendingEvents()
    {
        $this->eventSourcedObject->changeName('Malocher');
        
        $decorator = new ProtectedAccessDecorator();
        $decorator->manageObject($this->eventSourcedObject);
        
        $events = $decorator->getPendingEvents();
        
        $this->assertEquals(1, count($events));
        
        $userNameChangedEvent = $events[0];
        
        $this->assertInstanceOf('Malocher\EventStoreTest\Coverage\Mock\Event\UserNameChangedEvent', $userNameChangedEvent);
        
        $this->assertEquals('Malocher', $userNameChangedEvent->getPayload()['newName']);
        
        //Pending events should be reset after requesting them
        $this->assertEquals(0, count($decorator->getPendingEvents()));
    }

    public function testRegisterHandlers()
    {
        $this->eventSourcedObject->changeEmail('my.email@getmalocher.org');

        $this->assertEquals('my.email@getmalocher.org', $this->eventSourcedObject->getEmail());
    }

    public function testRegisterMissingHandlers()
    {
        $this->setExpectedException('Malocher\EventStore\EventSourcing\EventSourcingException');
        $emptyEventSourcedObject = new EmptyEventSourcedObject(1);
        $emptyEventSourcedObject->changeProp1('prop1_changed');
    }
    
    public function testConstructWithHistoryEvents()
    {
        $historyEvents = array();
        
        $userNameChangedEvent = new UserNameChangedEvent(
            array('oldName' => null, 'newName' => 'Malocher')
        );
        $userNameChangedEvent->setSourceId(1);
        $userNameChangedEvent->setSourceVersion(1);
        
        $historyEvents[] = $userNameChangedEvent;
        
        $userEmailChangedEvent = new UserEmailChangedEvent(
            array('oldEmail' => null, 'newEmail' => 'my.mail@getmalocher.org')
        );
        $userEmailChangedEvent->setSourceId(1);
        $userEmailChangedEvent->setSourceVersion(2);
        
        $historyEvents[] = $userEmailChangedEvent;
        
        $decorator = new ProtectedAccessDecorator();
        $decorator->constructManagedObjectFromHistory(
            'Malocher\EventStoreTest\Coverage\Mock\User', 
            '1', 
            $historyEvents
        );
        
        $mockUser = $decorator->getManagedObject();
        
        $this->assertEquals(2, $mockUser->getSourceVersion());
        $this->assertEquals('Malocher', $mockUser->getName());
        $this->assertEquals('my.mail@getmalocher.org', $mockUser->getEmail());
        
        //history events must not be treated as events that have to be stored
        $this->assertEquals(0, count($decorator->getPendingEvents()));
    }
    
    public function testGetAndSetSnapshot()
    {
        $this->eventSourcedObject->changeName('Malocher');
        $this->eventSourcedObject->changeEmail('my.mail@getmalocher.org');
        
        $decorator = new ProtectedAccessDecorator();
        $decorator->manageObject($this->eventSourcedObject);
        
        $snapshotEvent = $decorator->getSnapshot();
        
        $this->assertEquals(
            $this->eventSourcedObject->getSourceVersion(), 
            $snapshotEvent->getSourceVersion()
        );
        
        $this->assertEquals(
            $this->eventSourcedObject->getId(),
            $snapshotEvent->getSourceId()
        );
        
        $payloadCheck = array(
            'name' => 'Malocher',
            'email' => 'my.mail@getmalocher.org'
        );
        
        $this->assertEquals($payloadCheck, $snapshotEvent->getPayload());
        
        $history = array($snapshotEvent);
        
        $decorator = new ProtectedAccessDecorator();
        $decorator->constructManagedObjectFromHistory(
            'Malocher\EventStoreTest\Coverage\Mock\User', 
            '1', 
            $history
        );
        
        $mockUser = $decorator->getManagedObject();
        
        $this->assertEquals(
            $this->eventSourcedObject->getSourceVersion(), 
            $mockUser->getSourceVersion()
        );
        
        $this->assertEquals('Malocher', $mockUser->getName());
        $this->assertEquals('my.mail@getmalocher.org', $mockUser->getEmail());        
    }
}
