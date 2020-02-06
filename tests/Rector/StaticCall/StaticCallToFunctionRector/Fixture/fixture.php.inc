<?php

namespace Rector\Core\Tests\Rector\StaticCall\StaticCallToFunctionRector\Fixture;

use Rector\Core\Tests\Rector\StaticCall\StaticCallToFunctionRector\Source\SomeOldStaticClass;

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

namespace Rector\Core\Tests\Rector\StaticCall\StaticCallToFunctionRector\Fixture;

use Rector\Core\Tests\Rector\StaticCall\StaticCallToFunctionRector\Source\SomeOldStaticClass;

class SomeClass
{
    public function someMethod()
    {
        \view('template', []);
    }
}

?>
