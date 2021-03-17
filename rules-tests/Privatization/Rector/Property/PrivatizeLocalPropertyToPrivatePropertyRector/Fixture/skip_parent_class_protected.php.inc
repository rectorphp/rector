<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SkipParentClassProtected extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
