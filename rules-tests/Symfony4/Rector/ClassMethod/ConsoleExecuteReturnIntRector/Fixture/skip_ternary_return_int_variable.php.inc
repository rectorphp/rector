<?php

namespace Rector\Tests\Symfony4\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

function returns_bool()
{
    return rand(0, 1) === 0;
}

final class SkipTernaryReturnIntVariable extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $boolVariable = returns_bool();
        return $boolVariable ? 0 : 1;
    }
}
