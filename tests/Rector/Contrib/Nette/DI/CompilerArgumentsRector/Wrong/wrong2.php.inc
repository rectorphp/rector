<?php declare (strict_types=1);

class SomeClass
{
    public function run()
    {
        $compiler = new Nette\DI\Compiler;
        $compiler->compile($firstArg, $secondArg);

        $someOtherValue = 5;
    }
}
