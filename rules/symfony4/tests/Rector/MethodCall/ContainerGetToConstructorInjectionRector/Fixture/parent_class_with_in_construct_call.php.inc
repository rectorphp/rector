<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ThisClassCallsMethodInConstructor;

final class ParentClassWithInConstructCall extends ThisClassCallsMethodInConstructor
{
    protected function doSomethingFancy()
    {
        $someService = $this->getContainer()->get('translator');

        $translatedString = $this->getContainer()->get('translator')->translateSomething();
    }
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ThisClassCallsMethodInConstructor;

final class ParentClassWithInConstructCall extends ThisClassCallsMethodInConstructor
{
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $someTranslator)
    {
        parent::__construct();
    }
    protected function doSomethingFancy()
    {
        $someService = $this->someTranslator;

        $translatedString = $this->someTranslator->translateSomething();
    }
}

?>
