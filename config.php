<?php
return array(
    'adapter'                   => array(
        'Malocher\EventStore\Adapter\Doctrine\DoctrineDbalAdapter' => array(
            'user'=>'',
            'pass'=>'',
            'host'=>'',
            'port'=>''
        )
    ),
    'snapshot_interval'         => 20,
    'object_factory'            => 'Malocher\EventStore\EventSourcing\EventSourcedObjectFactory',
    'source_type_class_map'     => array(
        'user' => 'Malocher\EventStore\Entity\User'
    ),
    'auto_generate_snapshots'   => true,
    'snapshot_lookup'           => true
);