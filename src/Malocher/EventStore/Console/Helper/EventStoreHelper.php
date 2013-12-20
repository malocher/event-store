<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Console\Helper;

use Malocher\EventStore\EventStore;
use Symfony\Component\Console\Helper\Helper;

/**
 * Class EventStoreHelper
 *
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStore\Console\Helper
 */
class EventStoreHelper extends Helper
{
    /**
     * @var \Malocher\EventStore\EventStore
     */
    protected $es;

    /**
     * @param EventStore $es
     */
    public function __construct(EventStore $es)
    {
        $this->es = $es;
    }

    /**
     * @return EventStore
     */
    public function getEventStore()
    {
        return $this->es;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'es';
    }
}