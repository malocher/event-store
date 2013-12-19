<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Coverage\Mock;

use Malocher\EventStore\EventSourcing\AbstractEventSourced;
/**
 * User AggregateRoot used as Mock for AbstractEventSourced
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class User extends AbstractEventSourced
{
    protected $name;
    
    protected $email;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function changeName($newName)
    {
        $this->update(
            new Event\UserNameChangedEvent(array('oldName' => $this->name, 'newName' => $newName))
        );
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    
    public function changeEmail($newEmail)
    {
        $this->update(
            new Event\UserEmailChangedEvent(array('oldEmail' => $this->email, 'newEmail' => $newEmail))
        );
    }
    
    protected function registerHandlers() 
    {
        $this->handlers['UserNameChangedEvent'] = 'onNameChanged';
        $this->handlers['UserEmailChangedEvent'] = 'onEmailChanged';
    }
    
    protected function onNameChanged(Event\UserNameChangedEvent $e)
    {
        $this->name = $e->getPayload()['newName'];
    }
    
    protected function onEmailChanged(Event\UserEmailChangedEvent $e)
    {
        $this->email = $e->getPayload()['newEmail'];
    }
}
