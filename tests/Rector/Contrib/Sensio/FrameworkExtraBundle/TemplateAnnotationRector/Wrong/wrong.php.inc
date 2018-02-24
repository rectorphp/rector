<?php declare (strict_types=1);

namespace AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return [];
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}