<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReturnStaticFunctionCallCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return static::doSomething();
    }

    private static function doSomething()
    {
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

final class ReturnStaticFunctionCallCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return (int) static::doSomething();
    }

    private static function doSomething()
    {
        return null;
    }
}

?>
