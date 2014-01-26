<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Repository;

use Malocher\EventStore\EventStore;
use Malocher\EventStore\EventSourcing\EventSourcedObject;
/**
 *  EventSourcingRepository
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 * @package Malocher\EventStore\Repository
 */
class EventSourcingRepository implements RepositoryInterface
{
    /**
     * The EventStore instance
     * 
     * @var EventStore 
     */
    protected $eventStore;
    
    /**
     * FQCN of the EventSourced class for that the repository is responsible
     * 
     * @var string
     */
    protected $sourceFQCN;


    /**
     * {@inheritDoc}     
     */
    public function __construct(EventStore $eventStore, $sourceFQCN)
    {
        $this->eventStore = $eventStore;
        $this->sourceFQCN = $sourceFQCN;
    }

    /**
     * {@inheritDoc}     
     */
    public function find($sourceId)
    {
        return $this->eventStore->find($this->sourceFQCN, $sourceId);
    }

    /**
     * {@inheritDoc}     
     */
    public function save(EventSourcedObject $eventSourcedObject)
    {
        $this->eventStore->save($eventSourcedObject);
    }
}
