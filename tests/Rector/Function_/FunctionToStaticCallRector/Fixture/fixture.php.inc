<?php

namespace Rector\Core\Tests\FunctionToStaticCallRector;

class SomeClass
{
    public function someMethod()
    {
        \view('template', []);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\FunctionToStaticCallRector;

class SomeClass
{
    public function someMethod()
    {
        \SomeStaticClass::render('template', []);
    }
}

?>
