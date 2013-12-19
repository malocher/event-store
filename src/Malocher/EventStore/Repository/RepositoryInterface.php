<?php
/*
 * This file is part of the codeliner/event-store package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Repository;

use Malocher\EventStore\EventSourcing\EventSourcedInterface;
use Malocher\EventStore\EventStore;
/**
 * RepositoryInterface
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface RepositoryInterface
{
    /**
     * Construct
     * 
     * @param EventStore $eventStore
     * @param string     $sourceObjectType
     */
    public function __construct(EventStore $eventStore, $sourceObjectType);
    
    /**
     * Load an EventSourcedObject by it's id
     * 
     * @param string $sourceId
     * 
     * @return EventSourcedInterface
     */
    public function find($sourceId);
    
    /**
     * Save an EventSourcedObject
     * 
     * @param EventSourcedInterface $eventSourcedObject
     */
    public function save(EventSourcedInterface $eventSourcedObject);
}
