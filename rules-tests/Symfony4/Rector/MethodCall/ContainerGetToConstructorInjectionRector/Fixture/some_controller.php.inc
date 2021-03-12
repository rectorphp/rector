<?php

use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

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

use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

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
