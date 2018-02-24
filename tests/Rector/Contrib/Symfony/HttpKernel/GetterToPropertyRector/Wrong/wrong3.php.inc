<?php declare (strict_types=1);

class ClassWithNamedService3 implements ContainerAwareInterface
{
    public function render()
    {
        $someService = $this->get('some_service');
        $someResult = $this->get('some_service')->callMe();
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}