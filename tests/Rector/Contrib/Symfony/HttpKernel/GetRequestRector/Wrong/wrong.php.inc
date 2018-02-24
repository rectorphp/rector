<?php declare (strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClassWithNamedService extends Controller
{
    public function someAction()
    {
        $this->getRequest()->getSomething();
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}