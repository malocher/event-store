<?php
/*
 * This file is part of the malocher/event-store package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Malocher\EventStore\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class InfoCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('es:info')
            ->setDescription('display basic information')
            ->setHelp(<<<EOT
the <info>%command.name%</info> shows basic information.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln($input->getFirstArgument());
        return false;
    }
}