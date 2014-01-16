<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Adapter;

/**
 * AdapterException
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 * @package Malocher\EventStore\Adapter
 */
class AdapterException extends \Exception
{
    /**
     * Throw a configuration exception
     *
     * @param string $msg
     * @return AdapterException
     */
    public static function configurationException($msg)
    {
        return new self('[Adapter Configuration Error] ' . $msg . "\n");
    }
    
    /**
     * Throw an unsupported feature exception
     * 
     * @param string $msg
     * @return AdapterException
     */
    public static function unsupportedFeatureException($msg)
    {
        return new self('[Adapter unsupported Feature] ' . $msg . "\n");
    }
}
