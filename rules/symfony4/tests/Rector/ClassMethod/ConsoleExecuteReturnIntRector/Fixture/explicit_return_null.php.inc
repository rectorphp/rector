<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExplicitReturnNullCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // execute
        return null;
    }
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExplicitReturnNullCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // execute
        return 0;
    }
}

?>
