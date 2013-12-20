<?php
/*
 * This file is part of the codeliner/event-store package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\EventSourcing;

/**
 * EventSourcingException
 * 
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStore\EventSourcing
 */
class EventSourcingException extends \Exception
{
    /**
     * Throw a handler exception
     *
     * @param $msg
     * @return EventSourcingException
     */
    public static function handlerException($msg)
    {
        return new self('[EventSourcing Error] ' . $msg . "\n");
    }
}
