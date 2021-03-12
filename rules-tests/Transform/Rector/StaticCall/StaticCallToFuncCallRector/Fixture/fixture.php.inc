<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToFuncCallRector\Fixture;

use Rector\Transform\Tests\Rector\StaticCall\StaticCallToFuncCallRector\Source\SomeOldStaticClass;

class SomeClass
{
    public function someMethod()
    {
        SomeOldStaticClass::render('template', []);
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToFuncCallRector\Fixture;

use Rector\Transform\Tests\Rector\StaticCall\StaticCallToFuncCallRector\Source\SomeOldStaticClass;

class SomeClass
{
    public function someMethod()
    {
        \view('template', []);
    }
}

?>
