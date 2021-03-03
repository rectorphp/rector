<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SkipTernaryReturnIntVariableInIfElse extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $yes = 0;
        $no = 1;
        return rand(0, 1) ? $yes : $no;
    }
}
