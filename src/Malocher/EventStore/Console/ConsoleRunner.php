<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Console;

use Malocher\EventStore\Console\Command\SchemaInfoCommand;
use Malocher\EventStore\Console\Command\SchemaInstallCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

class ConsoleRunner
{
    const VERSION = 0.1;

    static public function run(HelperSet $helperSet, $commands = array())
    {
        $cli = new \Symfony\Component\Console\Application('Malocher EventStore Command Line Interface', self::VERSION);

        $cli->setHelperSet($helperSet);
        $cli->setCatchExceptions(true);
        self::addCommands($cli);
        $cli->addCommands($commands);
        $cli->run();
    }

    static public function addCommands(Application $cli)
    {
        $cli->addCommands(array(
            new SchemaInfoCommand(),
            new SchemaInstallCommand()
        ));
    }
}