<?php

namespace Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

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

namespace Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;
        parent::__construct();
    }
}

?>
