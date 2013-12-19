<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Coverage\EventSourcing;

use Malocher\EventStoreTest\Coverage\Mock\User;
use Malocher\EventStoreTest\Coverage\TestCase;
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
}
