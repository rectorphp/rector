<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\ParentWithParam;

class UsedButInClosure extends ParentWithParam{
    public function execute()
    {
        $x = function () {
            echo $foo ?? 'test';
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\ParentWithParam;

class UsedButInClosure extends ParentWithParam{
    public function execute($foo)
    {
        $x = function () {
            echo $foo ?? 'test';
        };
    }
}

?>
