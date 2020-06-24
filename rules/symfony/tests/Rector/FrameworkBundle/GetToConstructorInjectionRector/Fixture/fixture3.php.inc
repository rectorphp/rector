<?php

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\GetTrait;

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

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\GetTrait;

class ClassWithNamedServiceAndTrait
{
    use GetTrait;
    private \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $someTranslator;
    public function __construct(\Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $someTranslator)
    {
        $this->someTranslator = $someTranslator;
    }

    public function render()
    {
        $this->someTranslator;
    }
}

?>
