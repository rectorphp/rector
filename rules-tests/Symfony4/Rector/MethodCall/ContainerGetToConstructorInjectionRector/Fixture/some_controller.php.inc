<?php

use Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

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

use Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class SomeController extends ContainerAwareParentClass
{
    public function __construct(private \stdClass $stdClass)
    {
    }
    protected function execute()
    {
        $someService = $this->stdClass;
    }
}

?>
