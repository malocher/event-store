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
    public function addToStream($sourceFQCN, $sourceId, $events)
    {
        try {
            $this->conn->beginTransaction();
            foreach ($events as $event) {
                $this->insertEvent($sourceFQCN, $sourceId, $event);
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
    public function loadStream($sourceFQCN, $sourceId, $version = null)
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        
        $queryBuilder->select('*')->from($this->getTable($sourceFQCN), 'event')
            ->where('event.sourceId = :sourceId')
            ->orderBy('event.sourceVersion')
            ->setParameter('sourceId', $sourceId);
        
        if (!is_null($version)) {
            $queryBuilder->andWhere('event.sourceVersion >= :sourceVersion')
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
    public function createSnapshot($sourceFQCN, $sourceId, SnapshotEvent $event)
    {
        try {
            $this->conn->beginTransaction();
            
            $this->insertEvent($sourceFQCN, $sourceId, $event);
            
            $snapshotMetaData = array(
                'sourceType' => $sourceFQCN,
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
    public function getCurrentSnapshotVersion($sourceFQCN, $sourceId)
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        
        $queryBuilder->select('s.snapshotVersion')
            ->from($this->snapshotTable, 's')
            ->where('s.sourceType = :sourceType AND s.sourceId = :sourceId')
            ->setParameter('sourceType', $sourceFQCN)
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
     * @param string         $sourceFQCN
     * @param string         $sourceId
     * @param EventInterface $e
     * 
     * @return void
     */
    protected function insertEvent($sourceFQCN, $sourceId, EventInterface $e)
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

        $this->conn->insert($this->getTable($sourceFQCN), $eventData);
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
    protected function getTable($sourceFQCN)
    {
        if (isset($this->sourceTypeTableMap[$sourceFQCN])) {
            $tableName = $this->sourceTypeTableMap[$sourceFQCN];
        } else {
            $tableName = strtolower($this->getShortSourceType($sourceFQCN)) . "_stream";
        }
        
        return $tableName;
    }
    
    protected function getShortSourceType($sourceFQCN)
    {
        return join('', array_slice(explode('\\', $sourceFQCN), -1));
    }
}