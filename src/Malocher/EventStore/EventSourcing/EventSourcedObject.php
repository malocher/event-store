<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\EventSourcing;

/**
 * EventSourcedObject
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 * @package Malocher\EventStore\EventSourcing
 */
abstract class EventSourcedObject
{
    /**
     * Identifier
     * 
     * @var string 
     */
    protected $id;
    
    /**
     * Current version
     * 
     * @var float 
     */
    protected $version = 1;

    /**
     * Registered internal event handlers
     * 
     * @example array('My\DomainEvent' => 'onDomainEvent')
     * 
     * @var array 
     */
    protected $handlers = array();
    
    /**
     * List of events that are not commited to the EventStore
     * 
     * @var EventInterface[] 
     */
    protected $pendingEvents = array();    
    
    /**
     * Register internal event handler methods
     */
    abstract protected function registerHandlers();

    /**
     * Get the surrogate id used by the EventStore to identify the Object
     * 
     * @return string
     */
    protected function getId()
    {
        return $this->id;
    }
    
    /**
     * Set the surrogate id used by the EventStore to identify the Object
     * 
     * @param string $id
     */
    protected function setId($id)
    {
        $this->id = $id;
    }
    
    /**    
     * @param string $id
     * @param array $historyEvents
     */
    protected function initializeFromHistory($id, array $historyEvents)
    {
        $this->setId($id);
        $this->handlers['SnapshotEvent'] = 'onSnapshot';
        $this->replay($historyEvents);
    }

    /**
     * Get pending events
     * 
     * @return EventInterface[]
     */
    protected function getPendingEvents()
    {
        $pendingEvents = $this->pendingEvents;
        
        $this->pendingEvents = array();
        
        return $pendingEvents;
    }
    
    /**
     * Get a snapshot of current object state
     * 
     * @return SnapshotEvent
     */
    protected function getSnapshot()
    {
        $payload = $this->getSnapshotPayload();
        
        $snapshotEvent = new SnapshotEvent($payload);
        $snapshotEvent->setSourceId($this->getId());
        
        $this->version += 1;
        
        $snapshotEvent->setSourceVersion($this->version);
        
        return $snapshotEvent;
    }
    
    /**
     * Replay past events
     * 
     * @param EventInterface[] $historyEvents
     * 
     * @return void
     */
    protected function replay(array $historyEvents)
    {
        $this->registerHandlers();
        
        foreach ($historyEvents as $pastEvent) {
            $handler = $this->handlers[$this->determineEventName($pastEvent)];
            
            $this->{$handler}($pastEvent);
            
            $this->version = $pastEvent->getSourceVersion();
        }
    }
    
    /**
     * Update source with new event
     * 
     * @param EventInterface $e
     * 
     * @throws EventSourcingException
     */
    protected function update(EventInterface $e)
    {
        //lazy register handlers, if not done during construct
        if (empty($this->handlers)) {
            $this->registerHandlers();
        }
        
        $eventName = $this->determineEventName($e);
        
        if (!isset($this->handlers[$eventName])) {
            throw EventSourcingException::handlerException(
                sprintf(
                    'No handler method registered for Event: %s!',
                    $eventName
                )
            );
        }
        
        $handler = $this->handlers[$eventName];
        
        if (!method_exists($this, $handler)) {
            throw EventSourcingException::handlerException(
                sprintf(
                    'Handler: %s is no valid method of class: %s!',
                    $handler,
                    get_class($this)
                )
            );
        }
        
        $this->{$handler}($e);
        
        $e->setSourceId($this->getId());
        $this->version += 1;
        $e->setSourceVersion($this->version);
        $this->pendingEvents[] = $e;
    }
    
    /**
     * Determine event name
     * 
     * @param EventInterface $e
     * 
     * @return string
     */
    protected function determineEventName(EventInterface $e)
    {
        return join('', array_slice(explode('\\', get_class($e)), -1));
    }

    /**
     * Default method to get snapshot payload
     * 
     * Override this method if you need to serialize the payload
     * 
     * @return array
     */
    protected function getSnapshotPayload()
    {
        $vars = get_object_vars($this);
        
        unset($vars['id']);
        unset($vars['version']);
        unset($vars['handlers']);
        unset($vars['pendingEvents']);
        
        return $vars;
    }

    /**
     * @param SnapshotEvent $e
     */
    protected function onSnapshot(SnapshotEvent $e)
    {
        $vars = $e->getPayload();
        
        foreach ($vars as $property => $value) {
            $this->{$property} = $value;
        }
        
        $this->version = $e->getSourceVersion();
    }
}
