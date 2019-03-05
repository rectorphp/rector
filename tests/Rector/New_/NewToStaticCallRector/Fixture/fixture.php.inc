<?php

namespace Rector\Tests\Rector\New_\NewToStaticCallRector\Fixture;

use Rector\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;

class SomeClass
{
    public function run()
    {
        new FromNewClass($name);
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\New_\NewToStaticCallRector\Fixture;

use Rector\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;

class SomeClass
{
    public function run()
    {
        \Rector\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass::run($name);
    }
}

?>
