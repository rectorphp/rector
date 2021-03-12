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
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $someTranslator)
    {
    }
    public function render()
    {
        $this->someTranslator;
    }
}

?>
