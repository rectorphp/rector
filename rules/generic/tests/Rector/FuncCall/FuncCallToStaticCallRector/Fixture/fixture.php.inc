<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToStaticCallRector\Fixture;

class Fixture
{
    public function someMethod()
    {
        \view('template', []);
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToStaticCallRector\Fixture;

class Fixture
{
    public function someMethod()
    {
        \SomeStaticClass::render('template', []);
    }
}

?>
