<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StaticInExecute extends Command
{
    protected function configure()
    {
        $this->setName('some:command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This will fail
        SomeClass::staticMethodCall();
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StaticInExecute extends Command
{
    protected static $defaultName = 'some:command';
    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This will fail
        SomeClass::staticMethodCall();
    }
}

?>
