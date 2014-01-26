<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Malocher\EventStore\Console\ConsoleRunner;
use Malocher\EventStore\Console\Helper\EventStoreHelper;
use Malocher\EventStore\EventStore;
use Malocher\EventStore\Configuration\Configuration;
use Symfony\Component\Console\Helper\HelperSet;

(@include_once __DIR__ . '/../vendor/autoload.php') || @include_once __DIR__ . '/../../../autoload.php';

$directories = array(getcwd(), getcwd() . DIRECTORY_SEPARATOR . 'config');

$configFile = null;
foreach ($directories as $directory) {
    $configFile = $directory . DIRECTORY_SEPARATOR . 'eventstore.config.php';
    if (file_exists($configFile)) {
        break;
    }
}
if ( ! file_exists($configFile)) {
    echo 'You are missing a "config.php" or "config/eventstore.config.php" file in your project.';
    exit(1);
}
if ( ! is_readable($configFile)) {
    echo 'Configuration file [' . $configFile . '] does not have read permission.' . "\n";
    exit(1);
}

$commands = array();

$config = require $configFile;

$helperSet = new HelperSet(array(
    'es' => new EventStoreHelper(
        new EventStore(new Configuration($config))
    )
));

ConsoleRunner::run($helperSet, $commands);
