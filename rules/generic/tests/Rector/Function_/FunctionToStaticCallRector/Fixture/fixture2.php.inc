<?php

namespace Rector\Core\Tests\FunctionToStaticCallRector;

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

namespace Rector\Core\Tests\FunctionToStaticCallRector;

class SomeClass2
{
    public function someMethod()
    {
        \AnotherStaticClass::render('template', []);
    }
}

?>
