<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\StoreEvent;

use Symfony\Component\EventDispatcher\Event;
use Malocher\EventStore\EventSourcing\EventSourcedInterface;
use Malocher\EventStore\EventSourcing\EventInterface;
/**
 *  PostPersistEvent
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PostPersistEvent extends Event
{
    /**
     * @var array 
     */
    protected $persistedEvents = array();
    
    /**
     * @var EventSourcedInterface 
     */
    protected $eventSourcedObject;
    
    const NAME = 'eventstore.events_post_persist';
    
    /**
     * Construct
     * 
     * @param EventSourcedInterface $eventSourcedObject
     * @param EventInterface[]      $persistedEvents
     */
    public function __construct(EventSourcedInterface $eventSourcedObject, array $persistedEvents)
    {
        $this->eventSourcedObject = $eventSourcedObject;
        $this->persistedEvents = $persistedEvents;
    }
    
    /**
     * Get FQDN of EventSourcedObject
     * 
     * @return string
     */
    public function getSourceFQDN()
    {
        return get_class($this->eventSourcedObject);
    }
    
    /**
     * Get id of the EventSourcedObject
     * 
     * @return string
     */
    public function getSourceId()
    {
        return $this->eventSourcedObject->getId();
    }
    
    /**
     * Get the EventSourcedObject
     * 
     * @return EventSourcedInterface
     */
    public function getSource()
    {
        return $this->eventSourcedObject;
    }
    
    /**
     * Get the persisted events
     * 
     * @return EventInterface[]
     */
    public function getPersistedEvents()
    {
        return $this->persistedEvents;
    }
}
