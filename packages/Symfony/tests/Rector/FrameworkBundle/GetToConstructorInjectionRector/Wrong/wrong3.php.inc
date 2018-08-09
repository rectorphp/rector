<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\GetTrait;

class ClassWithNamedServiceAndTrait
{
    use GetTrait;

    public function render()
    {
        $this->get('translator');
    }
}
