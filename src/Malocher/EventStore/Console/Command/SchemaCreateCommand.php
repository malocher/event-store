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
 * Class SchemaCreateCommand
 *
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStore\Console\Command
 */
class SchemaCreateCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('schema:create')
            ->setDescription('Create EventStore schemas')
            ->addArgument('streams',InputArgument::IS_ARRAY | InputArgument::REQUIRED,'Names of streams to create')
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
        $streams = $input->getArgument('streams');

        $success = $this
            ->getHelper('es')
            ->getEventStore()
            ->getAdapter()
            ->createSchema($streams)
        ;

        // event dispatching ?!

        if($success){
            $output->writeln("<info>streams created</info>");
        } else {
            $output->writeln("<error>something went wrong</error>");
        }
    }
}