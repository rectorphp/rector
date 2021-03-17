<?php

namespace Rector\Tests\Transform\Rector\StaticCall\StaticCallToFuncCallRector\Fixture;

use Rector\Tests\Transform\Rector\StaticCall\StaticCallToFuncCallRector\Source\SomeOldStaticClass;

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

namespace Rector\Tests\Transform\Rector\StaticCall\StaticCallToFuncCallRector\Fixture;

use Rector\Tests\Transform\Rector\StaticCall\StaticCallToFuncCallRector\Source\SomeOldStaticClass;

class SomeClass
{
    public function someMethod()
    {
        \view('template', []);
    }
}

?>
