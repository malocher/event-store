<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallCommand
 * @package Malocher\EventStore\Console\Command
 */
class InstallCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('es:install')
            ->setDescription('Install EventStore')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $evenStore = $this->getHelper('es')->getEventStore();
        $adapter = $evenStore->getAdapter();
        $output->writeln("<info>".$adapter->install()."</info>");
    }
}