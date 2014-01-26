<?php
/*
 * This file is part of the codeliner/event-store package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\StoreEvent;

use Symfony\Component\EventDispatcher\Event;
use Malocher\EventStore\EventStore;
/**
 * Class PreCommitEvent
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PreCommitEvent extends Event
{
    const NAME = 'eventstore.events_pre_commit';
    
    /**
     *
     * @var EventStore
     */
    private $eventStore;
    
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }
    
    /**
     * 
     * @return EventStore
     */
    public function getEventStore()
    {
        return $this->eventStore;
    }
}
