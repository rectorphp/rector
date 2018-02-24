<?php declare (strict_types=1);

class ClassWithNamedService1 extends Controller
{
    /**
     */
    public function indexAction()
    {
        return $this->render('someFile.toBe.used');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}