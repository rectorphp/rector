<?php

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReturnExternalFunctionCall extends Command
{
    /** @var ExternalClass */
    private $external;

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->external->doSomething();
    }
}

final class ExternalCLass
{
    public function doSomething()
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

final class ReturnExternalFunctionCall extends Command
{
    /** @var ExternalClass */
    private $external;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->external->doSomething();
    }
}

final class ExternalCLass
{
    public function doSomething()
    {
        return 0;
    }
}

?>
