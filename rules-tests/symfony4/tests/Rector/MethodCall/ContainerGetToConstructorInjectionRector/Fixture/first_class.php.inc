<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

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

namespace Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class FirstClass extends ContainerAwareParentClass
{
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $someTranslator)
    {
    }
    protected function execute()
    {
        $someService = $this->someTranslator;

        $someService = $this->someTranslator->translateSomething();
    }
}

?>
