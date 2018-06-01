<?php declare (strict_types=1);

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector\Source\SymfonyController;

class ClassWithNamedService3 extends SymfonyController
{
    public function render()
    {
        $value = $this->getParameter('some_parameter');

        $query = new DoctrineQuery();
        $queryId = $query->getParameter('id');
    }
}
