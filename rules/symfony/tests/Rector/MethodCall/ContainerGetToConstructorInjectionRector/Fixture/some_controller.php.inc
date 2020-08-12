<?php

use Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class SomeController extends ContainerAwareParentClass
{
    protected function execute()
    {
        $someService = $this->getContainer()->get('some_service');
    }
}

?>
-----
<?php

use Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class SomeController extends ContainerAwareParentClass
{
    private \stdClass $stdClass;
    public function __construct(\stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
    protected function execute()
    {
        $someService = $this->stdClass;
    }
}

?>
