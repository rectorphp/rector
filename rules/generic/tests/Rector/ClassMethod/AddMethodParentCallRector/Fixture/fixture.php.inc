<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\AddMethodParentCallRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\AddMethodParentCallRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;
        parent::__construct();
    }
}

?>
