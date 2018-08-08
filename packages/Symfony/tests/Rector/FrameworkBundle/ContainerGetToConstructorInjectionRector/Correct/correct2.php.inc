<?php declare(strict_types=1);

use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class MyCommand extends ContainerAwareParentClass
{
    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslator
     */
    private $someTranslator;
    public function __construct(\Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslator $someTranslator)
    {
        $this->someTranslator = $someTranslator;
    }
    protected function execute()
    {
        $someService = $this->someTranslator;
    }
}
