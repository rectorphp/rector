<?php declare (strict_types=1);

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector\Source\SymfonyController;

final class ClassWithNamedService1 extends SymfonyController
{
    public function render()
    {
        $value = $this->getParameter('some_parameter');

        return [
            'value' => $this->getParameter('some_parameter'),
            'value2' => $value,
        ];
    }
}
