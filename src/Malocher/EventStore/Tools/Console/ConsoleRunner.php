<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Tools\Console;

use Malocher\EventStore\Tools\Console\Command\InfoCommand;
use Symfony\Component\Console\Application;

class ConsoleRunner
{
    static public function run($commands = array())
    {
        $cli = new \Symfony\Component\Console\Application('Malocher EventStore Command Line Interface', 1.0);
        $cli->setCatchExceptions(true);
        self::addCommands($cli);
        $cli->addCommands($commands);
        $cli->run();
    }

    static public function addCommands(Application $cli)
    {
        $cli->addCommands(array(
            new InfoCommand()
        ));
    }
}