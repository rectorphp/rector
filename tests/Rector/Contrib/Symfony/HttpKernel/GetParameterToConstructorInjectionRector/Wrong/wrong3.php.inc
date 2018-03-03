<?php declare (strict_types=1);

namespace SomeNamespace;

class ClassWithNamedService3 implements ContainerAwareInterface
{
    public function render()
    {
        $this->renderTwig([
            'value' => $this->container->getParameter('some_parameter')
        ]);
    }
}
