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
 * Default EventSourcedObjectFactory
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 * @package Malocher\EventStore\EventSourcing
 */
class EventSourcedObjectFactory 
{
    /**
     * Create new instance of EventSourcedObject referenced by id
     * 
     * @param string $sourceFQCN
     * @param string $id
     * @param array $history
     * 
     * @return EventSourcedObject
     */
    public function create($sourceFQCN, $id, array $history = null)
    {
        return new $sourceFQCN($id, $history);
    }
}
