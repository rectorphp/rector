<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MyCommand extends ContainerAwareParentCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $someService = $this->getContainer()->get('some_service');
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MyCommand extends ContainerAwareParentCommand
{
    public function __construct(private \stdClass $stdClass)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $someService = $this->stdClass;
    }
}

?>
