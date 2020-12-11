<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AConsoleCommand extends ACommand
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return null;
    }
}

class ACommand extends Command {}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AConsoleCommand extends ACommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}

class ACommand extends Command {}

?>
