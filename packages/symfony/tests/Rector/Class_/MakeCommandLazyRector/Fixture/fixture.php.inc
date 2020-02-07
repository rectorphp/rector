<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SunshineCommand extends \Symfony\Component\Console\Command\Command
{
    public function configure()
    {
        $this->setName('sunshine');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input->setName('trap');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SunshineCommand extends \Symfony\Component\Console\Command\Command
{
    protected static $defaultName = 'sunshine';
    public function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input->setName('trap');
    }
}

?>
