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
    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface
     */
    private $someTranslator;
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
