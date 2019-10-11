<?php

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReturnExternalStaticFunctionCall extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return ExternalStaticCLass::doSomething();
    }
}

final class ExternalStaticCLass
{
    public static function doSomething()
    {
        return null;
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReturnExternalStaticFunctionCall extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return ExternalStaticCLass::doSomething();
    }
}

final class ExternalStaticCLass
{
    public static function doSomething()
    {
        return 0;
    }
}

?>
