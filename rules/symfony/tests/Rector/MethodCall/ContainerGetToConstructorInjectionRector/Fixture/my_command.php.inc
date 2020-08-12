<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentCommand;
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

namespace Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MyCommand extends ContainerAwareParentCommand
{
    private \stdClass $stdClass;
    public function __construct(\stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $someService = $this->stdClass;
    }
}

?>
