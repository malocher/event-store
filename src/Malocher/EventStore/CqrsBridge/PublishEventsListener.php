<?php
/*
 * This file is part of the codeliner/event-store package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\CqrsBridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Malocher\EventStore\StoreEvent\PostPersistEvent;
use Malocher\Cqrs\Gate;
/**
 *  PublishEventsListener
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PublishEventsListener implements EventSubscriberInterface
{
    /**
     *
     * @var Gate 
     */
    protected $cqrsGate;
    
    public function __construct($configuration)
    {
        //@todo: Implement more flexible configuration options
        $this->cqrsGate = $configuration['gate'];
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            PostPersistEvent::NAME => array('onPostPersist', 0),
        );
    }

    public function onPostPersist(PostPersistEvent $e)
    {
        foreach ($e->getPersistedEvents() as $event) {
            //@todo: Define EventRouting via configuration, only call default bus
            //if no other routing can be found
            $this->cqrsGate->getBus()->publishEvent($event);
        }
    }
}
