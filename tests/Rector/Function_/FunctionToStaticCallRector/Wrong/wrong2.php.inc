<?php

namespace Rector\Tests\FunctionToStaticCallRector;

class SomeClass
{
    public function someMethod()
    {
        \SomeNamespaced\view('template', []);
    }
}
