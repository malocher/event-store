<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore;

use Malocher\EventStore\Adapter\AdapterInterface;
use Malocher\EventStore\Configuration\Configuration;
/**
 * EventStore 
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventStore 
{
    /**
     *
     * @var AdapterInterface 
     */
    protected $adapter;    
    
    protected $identityMap = array();
    protected $snapshotInterval;


    public function __construct(Configuration $config)
    {
        $this->adapter = $config->getAdapter();
        $this->snapshotInterval = $config->getSnapshotInterval();
    }
}
