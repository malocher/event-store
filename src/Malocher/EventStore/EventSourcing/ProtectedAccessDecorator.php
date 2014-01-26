<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\EventSourcing;

/**
 * Class ProtectedAccessDecorator
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 * @package Malocher\EventStore\EventSourcing
 */
class ProtectedAccessDecorator extends EventSourcedObject
{
    /**
     *
     * @var EventSourcedObject 
     */
    protected $managedEventSourcedObject;
    
    /**
     * @param EventSourcedObject $object
     */
    public function manageObject(EventSourcedObject $object)
    {
        $this->managedEventSourcedObject = $object;
    }
    
    /**
     * @param string $objectFQCN
     * @param string $objectId
     * @param array  $objectHistory
     */
    public function constructManagedObjectFromHistory($objectFQCN, $objectId, array $objectHistory)
    {
        $reflClass = new \ReflectionClass($objectFQCN);

        $this->managedEventSourcedObject = $reflClass->newInstanceWithoutConstructor();
        $this->managedEventSourcedObject->initializeFromHistory($objectId, $objectHistory);
    }
    
    /**
     * @return EventSourcedObject
     */
    public function getManagedObject()
    {
        return $this->managedEventSourcedObject;
    }
    
    /**
     * @return string
     */
    public function getId()
    {
        return $this->managedEventSourcedObject->getId();
    }
    
    /**
     * @return SnapshotEvent
     */
    public function getSnapshot()
    {
        return $this->managedEventSourcedObject->getSnapshot();
    }
    
    /**
     * @return EventInterface[]
     */
    public function getPendingEvents()
    {
        return $this->managedEventSourcedObject->getPendingEvents();
    }

    protected function registerHandlers()
    {
        //empty method to satisfy abstract contract
    }

}
