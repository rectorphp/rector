<?php

namespace Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source\GetTrait;

class ClassWithNamedServiceAndTrait
{
    use GetTrait;

    public function render()
    {
        $this->get('translator');
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source\GetTrait;

class ClassWithNamedServiceAndTrait
{
    use GetTrait;
    public function __construct(private \Rector\Tests\Symfony\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $someTranslator)
    {
    }

    public function render()
    {
        $this->someTranslator;
    }
}

?>
