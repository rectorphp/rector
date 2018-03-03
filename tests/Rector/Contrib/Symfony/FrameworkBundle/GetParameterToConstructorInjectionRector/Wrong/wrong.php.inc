<?php declare (strict_types=1);

namespace SomeNamespace;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ClassWithNamedService1 extends Controller
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
