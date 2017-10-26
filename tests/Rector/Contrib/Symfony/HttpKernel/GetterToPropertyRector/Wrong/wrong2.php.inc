<?php declare (strict_types=1);

class ClassWithNamedService2 implements ContainerAwareInterface
{
    public function render()
    {
        $someService = $this->get('some_service');
    }
}
