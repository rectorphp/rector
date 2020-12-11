<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SkipNonConsoleCommand extends NotACommand
{
    public function execute(string $foo, string $bar)
    {
        return null;
    }
}

class NotACommand {}
