<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToStaticCallRector\Fixture;

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

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToStaticCallRector\Fixture;

class Fixture
{
    public function someMethod()
    {
        \SomeStaticClass::render('template', []);
    }
}

?>
