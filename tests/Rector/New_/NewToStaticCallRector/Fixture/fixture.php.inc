<?php

namespace Rector\Core\Tests\Rector\New_\NewToStaticCallRector\Fixture;

use Rector\Core\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;

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

namespace Rector\Core\Tests\Rector\New_\NewToStaticCallRector\Fixture;

use Rector\Core\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;

class SomeClass
{
    public function run()
    {
        \Rector\Core\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass::run($name);
    }
}

?>
