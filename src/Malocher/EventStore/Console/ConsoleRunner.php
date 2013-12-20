<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Console;

use Malocher\EventStore\Console\Command\SchemaDropCommand;
use Malocher\EventStore\Console\Command\SchemaExportCommand;
use Malocher\EventStore\Console\Command\SchemaImportCommand;
use Malocher\EventStore\Console\Command\SchemaInfoCommand;
use Malocher\EventStore\Console\Command\SchemaCreateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Class ConsoleRunner
 *
 * @author Manfred Weber <crafics@php.net>
 * @package Malocher\EventStore\Console
 */
class ConsoleRunner
{
    /**
     * Version of CLI
     */
    const VERSION = 0.1;

    /**
     * @param HelperSet $helperSet
     * @param array $commands
     */
    static public function run(HelperSet $helperSet, $commands = array())
    {
        $cli = new \Symfony\Component\Console\Application('Malocher EventStore CLI', self::VERSION);

        $cli->setHelperSet($helperSet);
        $cli->setCatchExceptions(true);
        self::addCommands($cli);
        $cli->run();
    }

    /**
     * @param Application $cli
     */
    static public function addCommands(Application $cli)
    {
        $cli->addCommands(array(
            new SchemaInfoCommand(),
            new SchemaCreateCommand(),
            new SchemaDropCommand(),
            new SchemaImportCommand(),
            new SchemaExportCommand()
        ));
    }

}