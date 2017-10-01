<?php declare (strict_types=1);

class ClassWithNamedService implements ContainerAwareInterface
{
    public function render()
    {
        $someService = $this->get('some_service');
    }
}
