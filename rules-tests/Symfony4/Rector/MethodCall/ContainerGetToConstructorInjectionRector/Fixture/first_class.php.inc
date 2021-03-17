<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class FirstClass extends ContainerAwareParentClass
{
    protected function execute()
    {
        $someService = $this->getContainer()->get('translator');

        $someService = $this->getContainer()->get('translator')->translateSomething();
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class FirstClass extends ContainerAwareParentClass
{
    public function __construct(private \Rector\Tests\Symfony\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $someTranslator)
    {
    }
    protected function execute()
    {
        $someService = $this->someTranslator;

        $someService = $this->someTranslator->translateSomething();
    }
}

?>
