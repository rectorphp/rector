<?php

namespace Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\Fixture;

use Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;

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

namespace Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\Fixture;

use Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;

class SomeClass
{
    public function run()
    {
        \Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass::run($name);
    }
}

?>
