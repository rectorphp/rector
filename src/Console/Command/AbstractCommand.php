<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('debug')) {
            $this->getApplication()->setCatchExceptions(false);
        }
    }
}
