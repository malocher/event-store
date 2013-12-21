<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest;

use Malocher\EventStore\Adapter\Doctrine\DoctrineDbalAdapter;
use Malocher\EventStore\Adapter\AdapterInterface;
/**
 * TestCase for Malocher EventStore coverage tests
 *
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStoreTest
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var DoctrineDbalAdapter 
     */
    protected $doctrineDbalAdapter;
    
    protected function initEventStoreAdapter()
    {
        $options = array(
            'connection' => array(
                'driver' => 'pdo_sqlite',
                'memory' => true
            )
        );
        
        $this->doctrineDbalAdapter = new DoctrineDbalAdapter($options);
    }
    
    /**
     * @return AdapterInterface
     */
    protected function getEventStoreAdapter()
    {
        return $this->doctrineDbalAdapter;
    }
}
