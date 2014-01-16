<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Adapter\Feature;

/**
 * Interface TransactionFeatureInterface
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface TransactionFeatureInterface
{
    public function beginTransaction();
    
    public function commit();
    
    public function rollback();
}
