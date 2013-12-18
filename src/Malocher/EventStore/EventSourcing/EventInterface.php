<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\EventSourcing;

use Malocher\Cqrs\Event\EventInterface as CqrsEvent;
/**
 * EventInterface
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface EventInterface extends CqrsEvent
{
    /**
     * Set identifier of the related source
     * 
     * @param string $sourceId
     * 
     * @return void
     */
    public function setSourceId($sourceId);
    
    /**
     * Get identifier of the related source
     * 
     * @return string
     */
    public function getSourceId();
    
    /**
     * Set current version of the related source
     * 
     * @param integer $version
     * 
     * @return void
     */
    public function setSourceVersion($version);
    
    /**
     * Get the source version
     * 
     * @return integer
     */
    public function getSourceVersion();
}
