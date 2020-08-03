<?php

namespace Rector\Generic\Tests\Rector\Function_\FunctionToStaticCallRector\Fixture;

class SomeClass2
{
    public function someMethod()
    {
        \SomeNamespaced\view('template', []);
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Function_\FunctionToStaticCallRector\Fixture;

class SomeClass2
{
    public function someMethod()
    {
        \AnotherStaticClass::render('template', []);
    }
}

?>
