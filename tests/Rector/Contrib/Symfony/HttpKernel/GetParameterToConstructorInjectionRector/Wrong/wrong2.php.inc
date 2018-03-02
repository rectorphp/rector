<?php declare (strict_types=1);

namespace SomeNamespace;

class ClassWithNamedService2 implements ContainerAwareInterface
{
    public function render()
    {
        $this->renderTwig([
            'value' => $this->getParameter('some_parameter')
        ]);
    }
}
