<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\GetTrait;

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

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\GetTrait;

class ClassWithNamedServiceAndTrait
{
    use GetTrait;
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $someTranslator)
    {
    }

    public function render()
    {
        $this->someTranslator;
    }
}

?>
