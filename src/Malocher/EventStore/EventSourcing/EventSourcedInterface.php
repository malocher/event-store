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
 * EventSourcedInterface
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface EventSourcedInterface
{
    /**
     * Construct
     * 
     * @param string           $id
     * @param EventInterface[] $historyEvents
     */
    public function __construct($id, array $historyEvents = null);
    
    /**
     * Get non commited events
     * 
     * @return EventInterface[]
     */
    public function getPendingEvents();
    
    /**
     * Get id
     * 
     * @return string
     */
    public function getId();
    
    /**
     * Get a snapshot of current object state
     * 
     * @return SnapshotEvent
     */
    public function getSnapshot();
}
