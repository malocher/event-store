<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\EventSourcing;
use Malocher\EventStore\EventSourcing\ObjectChangedEvent;
use Malocher\EventStoreTest\TestCase;

/**
 * ObjectChangedEventTest
 * 
 * @author Manfred Weber <crafics@php.net>
 */
class ObjectChangedEventTest extends TestCase
{
    /**
     * @var ObjectChangedEvent
     */
    protected $objectChangedEvent;

    protected function setUp()
    {
        $this->objectChangedEvent = new ObjectChangedEvent();
    }

    public function testConstructed()
    {
        $this->assertInstanceOf('Malocher\EventStore\EventSourcing\ObjectChangedEvent', $this->objectChangedEvent);
    }

    public function testSetSourceId()
    {
        $this->objectChangedEvent->setSourceId(1);
        $this->assertEquals(1,$this->objectChangedEvent->getSourceId());
    }
    
    public function testGetSourceId()
    {
        $this->objectChangedEvent->setSourceId(2);
        $this->assertEquals(2,$this->objectChangedEvent->getSourceId());
    }

    public function testSetSourceVersion()
    {
        $this->objectChangedEvent->setSourceVersion(3);
        $this->assertEquals(3,$this->objectChangedEvent->getSourceVersion());
    }

    public function testGetSourceVersion()
    {
        $this->objectChangedEvent->setSourceVersion(4);
        $this->assertEquals(4,$this->objectChangedEvent->getSourceVersion());
    }
}
