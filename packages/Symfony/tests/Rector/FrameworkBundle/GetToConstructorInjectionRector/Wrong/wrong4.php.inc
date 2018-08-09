<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\ParentClassWithGetTrait;

class ClassWithNamedServiceAndParentTrait extends ParentClassWithGetTrait
{
    public function render()
    {
        $this->get('translator');
    }
}
