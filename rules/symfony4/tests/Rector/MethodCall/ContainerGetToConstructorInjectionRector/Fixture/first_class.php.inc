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
    private \Rector\Symfony4\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $someTranslator;
    public function __construct(\Rector\Symfony4\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $someTranslator)
    {
        $this->someTranslator = $someTranslator;
    }
    protected function execute()
    {
        $someService = $this->someTranslator;

        $someService = $this->someTranslator->translateSomething();
    }
}

?>
