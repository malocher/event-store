<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\EventSourcing;

use Malocher\Cqrs\Message\Message;
/**
 * SnapshotEvent
 * 
 * Special type of EventInterface that represents a full snapshot of a source
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 * @package Malocher\EventStore\EventSourcing
 */
class SnapshotEvent extends Message implements EventInterface
{
    protected $sourceId;
    
    /**
     * Set id of related EventSourced object
     * 
     * @param string $sourceId
     * 
     * @return void
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
    }
    
    /**
     * Get id of the related EventSourced object
     * 
     * @return string|null
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }
    
    /**
     * Set current version of the related source
     * 
     * @param integer $version
     * 
     * @return void
     */
    public function setSourceVersion($version)
    {
        $this->sourceVersion = $version;
    }

    /**
     * Get the source version
     * 
     * @return integer
     */
    public function getSourceVersion()
    {
        return $this->sourceVersion;
    }
}
