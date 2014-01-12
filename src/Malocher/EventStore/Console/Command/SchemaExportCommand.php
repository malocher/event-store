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
 * Class SchemaExportCommand
 *
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStore\Console\Command
 */
class SchemaExportCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('schema:export')
            ->setDescription('Export EventStore schemas')
            ->addArgument('file',InputArgument::REQUIRED,'Path to export file')
            ->addOption('snapshots-only',null,InputOption::VALUE_NONE,'Export snapshots only or complete history?');
        ;
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $snapshots_only = $input->getOption('snapshots-only');
        $file = $input->getArgument('file');
        $success = $this
            ->getHelper('es')
            ->getEventStore()
            ->getAdapter()
            ->exportSchema($file,$snapshots_only)
        ;

        // event dispatching ?!

        if($success){
            $output->writeln("<info>schemas exported</info>");
        } else {
            $output->writeln("<error>something went wrong</error>");
        }
    }
}