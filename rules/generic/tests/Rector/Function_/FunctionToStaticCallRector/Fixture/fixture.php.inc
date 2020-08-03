<?php

namespace Rector\Generic\Tests\Rector\Function_\FunctionToStaticCallRector\Fixture;

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

namespace Rector\Generic\Tests\Rector\Function_\FunctionToStaticCallRector\Fixture;

class SomeClass
{
    public function someMethod()
    {
        \SomeStaticClass::render('template', []);
    }
}

?>
