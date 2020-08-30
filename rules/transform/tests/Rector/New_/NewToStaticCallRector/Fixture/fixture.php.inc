<?php

namespace Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;

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

namespace Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;

class SomeClass
{
    public function run()
    {
        \Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass::run($name);
    }
}

?>
