<?php declare (strict_types=1);

class ClassWithNamedService4 implements ContainerAwareInterface
{
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->get('some_service')->callMe()
        ]);
    }
}
