<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\EventSourcing;

use Malocher\Cqrs\Event\EventInterface as CqrsEvent;

/**
 * Interface EventInterfaceTest
 * @package Malocher\EventStoreTest\EventSourcing
 */
interface EventInterfaceTest extends CqrsEvent
{
    //public function setSourceId($sourceId);
    
    //public function getSourceId();
    
    //public function setSourceVersion($version);
    
    //public function getSourceVersion();
}
