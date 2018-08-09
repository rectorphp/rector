<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\GetTrait;

class ClassWithNamedServiceAndTrait
{
    use GetTrait;
    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslator
     */
    private $someTranslator;
    public function __construct(\Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslator $someTranslator)
    {
        $this->someTranslator = $someTranslator;
    }

    public function render()
    {
        $this->someTranslator;
    }
}
