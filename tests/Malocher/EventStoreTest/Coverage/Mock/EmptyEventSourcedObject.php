<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Coverage\Mock;

use Malocher\EventStore\EventSourcing\EventSourcedObject;
/**
 * User EmptyEventSourcedObject used as Mock for AbstractEventSourced
 * 
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStoreTest\Coverage\Mock
 */
class EmptyEventSourcedObject extends EventSourcedObject
{
    protected $prop1;

    public function getProp1()
    {
        return $this->prop1;
    }

    public function changeProp1($newProp1)
    {
        $this->update(
            new Event\Prop1ChangedEvent(array('oldProp1' => $this->prop1, 'newProp1' => $newProp1))
        );
    }
}
