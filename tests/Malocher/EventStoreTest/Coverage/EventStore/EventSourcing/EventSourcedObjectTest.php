<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Coverage\EventStore\EventSourcing;

use Malocher\EventStoreTest\Coverage\Mock\User;
use Malocher\EventStoreTest\Coverage\Mock\Event\UserNameChangedEvent;
use Malocher\EventStoreTest\Coverage\Mock\Event\UserEmailChangedEvent;
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
        $this->eventSourcedObject = new User(1);
    }

    public function testGetId()
    {
        $this->assertEquals( 1, $this->eventSourcedObject->getId() );
    }

    /*
    public function getPendingEvents()
    {
        $pendingEvents = $this->pendingEvents;

        $this->pendingEvents = array();

        return $pendingEvents;
    }

    public function getSnapshot()
    {
        $payload = $this->getSnapshotPayload();

        $snapshotEvent = new SnapshotEvent($payload);
        $snapshotEvent->setSourceId($this->getId());

        $this->version += 1;

        $snapshotEvent->setSourceVersion($this->version);

        return $snapshotEvent;
    }

    protected function registerHandlers()
    {
    }

    protected function loadHistory(array $historyEvents)
    {
        foreach ($historyEvents as $pastEvent) {
            $handler = $this->handlers[$this->determineEventName($pastEvent)];

            $this->{$handler}($pastEvent);

            $this->version = $pastEvent->getSourceVersion();
        }
    }

    protected function update(EventInterface $e)
    {
        $handler = $this->handlers[$this->determineEventName($e)];
        $this->{$handler}($e);

        $e->setSourceId($this->getId());
        $this->version += 1;
        $e->setSourceVersion($this->version);
        $this->pendingEvents[] = $e;
    }

    protected function determineEventName(EventInterface $e)
    {
        return join('', array_slice(explode('\\', get_class($e)), -1));
    }

    protected function getSnapshotPayload()
    {
        $vars = get_object_vars($this);

        unset($vars['id']);
        unset($vars['version']);
        unset($vars['handlers']);
        unset($vars['pendingEvents']);

        return $vars;
    }

    protected function onSnapshot(SnapshotEvent $e)
    {
        $vars = $e->getPayload();

        foreach ($vars as $property => $value) {
            $this->{$property} = $value;
        }

        $this->version = $e->getSourceVersion();
    }
    */
    
    public function testGetPendingEvents()
    {
        $this->eventSourcedObject->changeName('Malocher');
        
        $events = $this->eventSourcedObject->getPendingEvents();
        
        $this->assertEquals(1, count($events));
        
        $userNameChangedEvent = $events[0];
        
        $this->assertInstanceOf('Malocher\EventStoreTest\Coverage\Mock\Event\UserNameChangedEvent', $userNameChangedEvent);
        
        $this->assertEquals('Malocher', $userNameChangedEvent->getPayload()['newName']);
        
        //Pending events should be reset after requesting them
        $this->assertEquals(0, count($this->eventSourcedObject->getPendingEvents()));
    }
    
    public function testRegisterHandlers()
    {
        $this->eventSourcedObject->changeEmail('my.email@getmalocher.org');
        
        $this->assertEquals('my.email@getmalocher.org', $this->eventSourcedObject->getEmail());
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
        
        $mockUser = new User(1, $historyEvents);
        
        $this->assertEquals(2, $mockUser->getSourceVersion());
        $this->assertEquals('Malocher', $mockUser->getName());
        $this->assertEquals('my.mail@getmalocher.org', $mockUser->getEmail());
        
        //history events must not be treated as events that have to be stored
        $this->assertEquals(0, count($mockUser->getPendingEvents()));
    }
    
    public function testGetAndSetSnapshot()
    {
        $this->eventSourcedObject->changeName('Malocher');
        $this->eventSourcedObject->changeEmail('my.mail@getmalocher.org');
        $snapshotEvent = $this->eventSourcedObject->getSnapshot();
        
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
        
        $mockUser = new User(1, $history);
        
        $this->assertEquals(
            $this->eventSourcedObject->getSourceVersion(), 
            $mockUser->getSourceVersion()
        );
        
        $this->assertEquals('Malocher', $mockUser->getName());
        $this->assertEquals('my.mail@getmalocher.org', $mockUser->getEmail());        
    }
}
