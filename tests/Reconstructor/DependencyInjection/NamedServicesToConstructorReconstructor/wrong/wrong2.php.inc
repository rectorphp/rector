<?php declare (strict_types=1);

class ClassWitNamedService implements ContainerAwareInterface
{
    public function render()
    {
        $someService = $this->get('some.class');
    }
}
