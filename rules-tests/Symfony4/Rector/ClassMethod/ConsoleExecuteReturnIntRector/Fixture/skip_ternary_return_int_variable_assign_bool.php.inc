<?php

namespace Rector\Tests\Symfony4\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SkipTernaryReturnIntVariableAssignBool extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $boolVariable = true;
        return $boolVariable ? 0 : 1;
    }
}
