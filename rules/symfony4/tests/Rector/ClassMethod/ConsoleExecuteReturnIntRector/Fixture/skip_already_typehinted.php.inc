<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSkipAlreadyTypeHintedCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output):int
    {
        return 1;
    }
}

final class SkipAlreadyTypeHintedCommand extends AbstractSkipAlreadyTypeHintedCommand
{
    protected static $defaultName = 'shq:user:create';

    public function execute(InputInterface $input, OutputInterface $output):int
    {
        $initialized = parent::execute($input, $output);
        if (0 !== $initialized) {
            return (int) $initialized;
        }

        return 0;
    }
}

