<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Coverage\EventStore;

use Malocher\EventStore\Configuration\Configuration;
use Malocher\EventStore\EventStore;
use Malocher\EventStoreTest\TestCase;

/**
 * EventStoreTest
 * 
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStoreTest\Coverage\EventStore
 */
class EventStoreTest extends TestCase
{
    /**
     * @var EventStore
     */
    private $eventStore;

    public function setUp()
    {
        $config = array(
            'adapter' => '',
            'snapshot_interval' => '',
        );
        $configuration = new Configuration($config);
        $this->eventStore = new EventStore($configuration);
    }

    public function testConstructed()
    {
        $this->assertInstanceOf('Malocher\EventStore\EventStore', $this->eventStore);
        //$this->adapter = $config->getAdapter();
        //$this->snapshotInterval = $config->getSnapshotInterval();
    }
}
