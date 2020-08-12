<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ThisClassCallsMethodInConstructor;

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

namespace Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ThisClassCallsMethodInConstructor;

final class ParentClassWithInConstructCall extends ThisClassCallsMethodInConstructor
{
    private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $someTranslator;
    public function __construct(\Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $someTranslator)
    {
        $this->someTranslator = $someTranslator;
        parent::__construct();
    }
    protected function doSomethingFancy()
    {
        $someService = $this->someTranslator;

        $translatedString = $this->someTranslator->translateSomething();
    }
}

?>
