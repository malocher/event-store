<?php
/*
 * This file is part of the codeliner/event-store package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStoreTest\Adapter;

use Malocher\EventStoreTest\TestCase;
use Malocher\EventStore\Adapter\Doctrine\DoctrineDbalAdapter;
use Malocher\EventStoreTest\Coverage\Mock\Event\UserNameChangedEvent;
use Malocher\EventStoreTest\Coverage\Mock\Event\UserEmailChangedEvent;
use Malocher\EventStore\EventSourcing\SnapshotEvent;
/**
 * DoctrineDbalAdapterTest
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DoctrineDbalAdapterTest extends TestCase 
{
    /**
     *
     * @var DoctrineDbalAdapter 
     */
    protected $doctrineDbalAdapter;


    protected function setUp() 
    {
        $this->initEventStoreAdapter();
        $this->createStream('user_stream');
        $this->doctrineDbalAdapter = $this->getEventStoreAdapter();
    }
    
    public function testAddToStreamAndLoadStream()
    {
        $yesterdayTimestamp = time() - 86400;
        $userNameChangedEvent = new UserNameChangedEvent(array('name' => 'Malocher'), '100', $yesterdayTimestamp, 2.0);
        $userNameChangedEvent->setSourceId('1');
        $userNameChangedEvent->setSourceVersion(1);
        
        $userEmailChangedEvent = new UserEmailChangedEvent(array('email' => 'my.mail@getmalocher.org'), '101', $yesterdayTimestamp, 2.0);
        $userEmailChangedEvent->setSourceId('1');
        $userEmailChangedEvent->setSourceVersion(2);
        
        $this->doctrineDbalAdapter->addToStream('User', '1', array($userNameChangedEvent, $userEmailChangedEvent));
        
        $stream = $this->doctrineDbalAdapter->loadStream('User', '1');
        
        $this->assertEquals(array($userNameChangedEvent, $userEmailChangedEvent), $stream);
    }
    
    public function testCreateSnapshotAndGetCurrentSnapshotVersion()
    {
        $this->assertEquals(0, $this->doctrineDbalAdapter->getCurrentSnapshotVersion('User', '1'));
        
        $yesterdayTimestamp = time() - 86400;
        $userNameChangedEvent = new UserNameChangedEvent(array('name' => 'Malocher'), '100', $yesterdayTimestamp, 2.0);
        $userNameChangedEvent->setSourceId('1');
        $userNameChangedEvent->setSourceVersion(1);
        
        $userEmailChangedEvent = new UserEmailChangedEvent(array('email' => 'my.mail@getmalocher.org'), '101', $yesterdayTimestamp, 2.0);
        $userEmailChangedEvent->setSourceId('1');
        $userEmailChangedEvent->setSourceVersion(2);
        
        $this->doctrineDbalAdapter->addToStream('User', '1', array($userNameChangedEvent, $userEmailChangedEvent));
        
        $snapshotEvent = new SnapshotEvent(array('name' => 'Malocher', 'email' => 'my.mail@getmalocher.org'), '102', $yesterdayTimestamp, 2.0);
        $snapshotEvent->setSourceId('1');
        $snapshotEvent->setSourceVersion(3);
        
        $this->doctrineDbalAdapter->createSnapshot('User', '1', $snapshotEvent);
        
        $this->assertEquals(3, $this->doctrineDbalAdapter->getCurrentSnapshotVersion('User', '1'));
        
        $this->assertEquals(
            array($userNameChangedEvent, $userEmailChangedEvent, $snapshotEvent), 
            $this->doctrineDbalAdapter->loadStream('User', '1')
        );
    }
    
    public function testLoadStreamFromVersionOn()
    {
        $yesterdayTimestamp = time() - 86400;
        $userNameChangedEvent = new UserNameChangedEvent(array('name' => 'Malocher'), '100', $yesterdayTimestamp, 2.0);
        $userNameChangedEvent->setSourceId('1');
        $userNameChangedEvent->setSourceVersion(1);
        
        $userEmailChangedEvent = new UserEmailChangedEvent(array('email' => 'my.mail@getmalocher.org'), '101', $yesterdayTimestamp, 2.0);
        $userEmailChangedEvent->setSourceId('1');
        $userEmailChangedEvent->setSourceVersion(2);
        
        $this->doctrineDbalAdapter->addToStream('User', '1', array($userNameChangedEvent, $userEmailChangedEvent));
        
        $snapshotEvent = new SnapshotEvent(array('name' => 'Malocher', 'email' => 'my.mail@getmalocher.org'), '102', $yesterdayTimestamp, 2.0);
        $snapshotEvent->setSourceId('1');
        $snapshotEvent->setSourceVersion(3);
        
        $this->doctrineDbalAdapter->createSnapshot('User', '1', $snapshotEvent);
        
        $userEmailChangedEvent2 = new UserEmailChangedEvent(array('email' => 'contact@getmalocher.org'), '103', $yesterdayTimestamp, 2.0);
        $userEmailChangedEvent2->setSourceId('1');
        $userEmailChangedEvent2->setSourceVersion(4);
        
        $this->doctrineDbalAdapter->addToStream('User', '1', array($userEmailChangedEvent2));
        
        $this->assertEquals(
            array($snapshotEvent, $userEmailChangedEvent2), 
            $this->doctrineDbalAdapter->loadStream(
                'User', 
                '1', 
                $this->doctrineDbalAdapter->getCurrentSnapshotVersion('User', '1')
            )
        );
    }
}
