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

class EventStoreHelper extends Helper
{
    protected $es;

    public function __construct(EventStore $es)
    {
        $this->es = $es;
    }

    public function getEventStore()
    {
        return $this->es;
    }

    public function getName()
    {
        return 'es';
    }
}