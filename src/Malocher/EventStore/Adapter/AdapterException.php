<?php
/*
 * This file is part of the codeliner/event-store package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Adapter;

/**
 * AdapterException
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AdapterException extends \Exception
{
    public static function configurationException($msg)
    {
        return new self('[Adapter Configuration Error] ' . $msg . "\n");
    }
}
