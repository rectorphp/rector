<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\ParentClassWithGetTrait;

class ClassWithNamedServiceAndParentTrait extends ParentClassWithGetTrait
{
    public function render()
    {
        $this->get('translator');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\ParentClassWithGetTrait;

class ClassWithNamedServiceAndParentTrait extends ParentClassWithGetTrait
{
    private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $someTranslator;
    public function __construct(\Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $someTranslator)
    {
        $this->someTranslator = $someTranslator;
    }
    public function render()
    {
        $this->someTranslator;
    }
}

?>
