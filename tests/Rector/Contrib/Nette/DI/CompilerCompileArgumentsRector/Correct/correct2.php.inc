<?php declare (strict_types=1);

class SomeClass
{
    public function run()
    {
        $compiler = new Nette\DI\Compiler;
        $compiler->compile();
        $compiler->addConfig($firstArg);
        $compiler->setClassName($secondArg);

        $someOtherValue = 5;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}