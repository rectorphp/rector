<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SkipAlreadyInt extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return 2;
    }
}
