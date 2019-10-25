<?php

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class NonConsoleCommand extends NotACommand
{
    public function execute(string $foo, string $bar)
    {
        return null;
    }
}

class NotACommand {}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class NonConsoleCommand extends NotACommand
{
    public function execute(string $foo, string $bar)
    {
        return null;
    }
}

class NotACommand {}

?>
