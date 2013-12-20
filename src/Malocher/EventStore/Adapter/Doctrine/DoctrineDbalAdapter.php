<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Adapter\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Malocher\EventStore\Adapter\AdapterInterface;
use Malocher\EventStore\Adapter\AdapterException;
use Malocher\EventStore\EventSourcing\EventInterface;
use Malocher\EventStore\EventSourcing\SnapshotEvent;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
/**
 * DoctrineAdapter
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DoctrineDbalAdapter implements AdapterInterface
{
    /**
     * Doctrine DBAL connection
     * 
     * @var Connection
     */
    protected $conn;
    
    /**
     *
     * @var Serializer
     */
    protected $serializer;
    
    /**
     * Custom sourceType to table mapping
     * 
     * @var array 
     */
    protected $sourceTypeTableMap = array();
    
    /**
     * Name of the table that contains snapshot metadata
     * 
     * @var string 
     */
    protected $snapshotTable = 'snapshot';

    /**
     * {@inheritDoc}
     */
    public function __construct(array $options)
    {
        if (!isset($options['connection'])) {
            throw AdapterException::configurationException('Missing connection configuration');
        }
        
        if (isset($options['serializer'])) {
            $this->serializer = $options['serializer'];
        }
        
        if (isset($options['source_table_map'])) {
            $this->sourceTypeTableMap = $options['source_table_map'];
        }
        
        if (isset($options['snapshot_table'])) {
            $this->snapshotTable = $options['snapshot_table'];
        }
        
        $this->conn = DriverManager::getConnection($options['connection']);
    }

    /**
     * install
     */
    public function install()
    {
        return "Doctrine DbalAdapter ... install schema";
    }

    /**
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * {@inheritDoc}
     */
    public function addToStream($sourceType, $sourceId, $events)
    {
        try {
            $this->conn->beginTransaction();
            foreach ($events as $event) {
                $this->insertEvent($sourceType, $sourceId, $event);
            }
            $this->conn->commit();
        } catch (\Exception $ex) {
            $this->conn->rollBack();
            throw $ex;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function loadStream($sourceType, $sourceId, $version = null)
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        
        $queryBuilder->select('*')->from($this->getTable($sourceType), 'event')
            ->where('event.sourceId = :sourceId')
            ->orderBy('event.sourceVersion')
            ->setParameter('sourceId', $sourceId);
        
        if (!is_null($version)) {
            $queryBuilder->andWhere('event.sourceVersion = :sourceVersion')
                ->setParameter('sourceVersion', $version);
        }
        
        $eventsData = $queryBuilder->execute()->fetchAll();
        
        $events = array();
        
        foreach ($eventsData as $eventData) {
            $eventClass = $eventData['eventClass'];
            
            $payload = $this->getSerializer()->deserialize($eventData['payload'], 'array', 'json');
            
            $event = new $eventClass($payload, $eventData['eventId'], (int)$eventData['timestamp'], (float)$eventData['eventVersion']);
            $event->setSourceVersion((int)$eventData['sourceVersion']);
            $event->setSourceId($sourceId);
            
            $events[] = $event;
        }
        
        return $events;
    }
    
    /**
     * {@inheritDoc}
     */
    public function createSnapshot($sourceType, $sourceId, SnapshotEvent $event)
    {
        try {
            $this->conn->beginTransaction();
            
            $this->insertEvent($sourceType, $sourceId, $event);
            
            $snapshotMetaData = array(
                'sourceType' => $sourceType,
                'sourceId' => $sourceId,
                'snapshotVersion' => $event->getSourceVersion()
            );
            
            $this->conn->insert($this->snapshotTable, $snapshotMetaData);
            
            $this->conn->commit();
        } catch (\Exception $ex) {
            $this->conn->rollback();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentSnapshotVersion($sourceType, $sourceId)
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        
        $queryBuilder->select('s.snapshotVersion')
            ->from($this->snapshotTable, 's')
            ->where('s.sourceType = :sourceType AND s.sourceId = :sourceId')
            ->setParameter('sourceType', $sourceType)
            ->setParameter('sourceId', $sourceId);
        
        $row = $queryBuilder->execute()->fetch(\PDO::FETCH_ASSOC);;
        
        if ($row) {
            return (int)$row['snapshotVersion'];
        }
        
        return 0;
    }

    /**
     * Insert an event
     * 
     * @param string         $sourceType
     * @param string         $sourceId
     * @param EventInterface $e
     * 
     * @return void
     */
    protected function insertEvent($sourceType, $sourceId, EventInterface $e)
    {        
        $eventData = array(
            'sourceId' => $sourceId,
            'sourceVersion' => $e->getSourceVersion(),
            'eventClass' => get_class($e),
            'payload' => $this->getSerializer()->serialize($e->getPayload(), 'json'),
            'eventId' => $e->getId(),
            'eventVersion' => $e->getVersion(),
            'timestamp' => $e->getTimestamp()
        );

        $this->conn->insert($this->getTable($sourceType), $eventData);
    }

    /**
     * 
     * @return Serializer
     */
    protected function getSerializer()
    {
        if (is_null($this->serializer)) {
            $this->serializer = SerializerBuilder::create()->build();
        }
        
        return $this->serializer;
    }
    
    /**
     * Get tablename for given sourceType
     * 
     * @param string $sourceType
     * 
     * @return string
     */
    protected function getTable($sourceType)
    {
        if (isset($this->sourceTypeTableMap[$sourceType])) {
            $tableName = $this->sourceTypeTableMap[$sourceType];
        } else {
            $tableName = strtolower($sourceType) . "_stream";
        }
        
        return $tableName;
    }
}