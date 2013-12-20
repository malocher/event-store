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
 */
class EventSourcedObject implements EventSourcedInterface
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
     * Construct
     * 
     * @param string           $id
     * @param EventInterface[] $historyEvents
     */
    public function __construct($id, array $historyEvents = null)
    {
        $this->id = $id;   
        
        $this->handlers['SnapshotEvent'] = 'onSnapshot';
        $this->registerHandlers(); 
        
        if (is_array($historyEvents)) {
            $this->replay($historyEvents);
        }
    }

    /**
     * Get the identifier
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get pending events
     * 
     * @return EventInterface[]
     */
    public function getPendingEvents()
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
    public function getSnapshot()
    {
        $payload = $this->getSnapshotPayload();
        
        $snapshotEvent = new SnapshotEvent($payload);
        $snapshotEvent->setSourceId($this->getId());
        
        $this->version += 1;
        
        $snapshotEvent->setSourceVersion($this->version);
        
        return $snapshotEvent;
    }
    
    /**
     * Hookpoint to register event handlers
     * 
     * Method is called during construct
     * 
     * @return void
     */
    protected function registerHandlers()
    {
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
     */
    protected function update(EventInterface $e)
    {
        $handler = $this->handlers[$this->determineEventName($e)];
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
    
    protected function onSnapshot(SnapshotEvent $e)
    {
        $vars = $e->getPayload();
        
        foreach ($vars as $property => $value) {
            $this->{$property} = $value;
        }
        
        $this->version = $e->getSourceVersion();
    }
}
