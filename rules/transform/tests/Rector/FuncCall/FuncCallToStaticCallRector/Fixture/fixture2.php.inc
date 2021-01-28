<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToStaticCallRector\Fixture;

class Fixture2
{
    public function someMethod()
    {
        \SomeNamespaced\view('template', []);
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToStaticCallRector\Fixture;

class Fixture2
{
    public function someMethod()
    {
        \AnotherStaticClass::render('template', []);
    }
}

?>
