<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Adapter;

use Malocher\EventStore\EventSourcing\EventInterface;
use Malocher\EventStore\EventSourcing\SnapshotEvent;
/**
 * Interface of an EventStore Adapter
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AdapterInterface
{
    /**
     * Load EventStream of an EventSourced object from given version on
     * 
     * Pass null as version to get the complete stream
     * 
     * @param string $sourceType
     * @param string $sourceId
     * @param float  $version
     * 
     * @return EventInterface
     */
    public function loadStream($sourceType, $sourceId, $version = null);
    
    /**
     * Add events to the source stream
     * 
     * @param string           $sourceType
     * @param string           $sourceId
     * @param EventInterface[] $events
     * 
     * @return void
     */
    public function addToStream($sourceType, $sourceId, $events);
    
    /**
     * Add snapshot to stream and create reference to the version of the snapshot
     */
    public function createSnapshot($sourceType, $sourceId, SnapshotEvent $event);
    
    /**
     * Get the current snapshot version of given source
     * 
     * @return float
     */
    public function getCurrentSnapshotVersion($sourceType, $sourceId);
}
