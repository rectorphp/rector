<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\ParentWithParam;

// ref https://3v4l.org/BPFsU
class ChildMoreParamsNoDefaultValue extends ParentWithParam{
    public function execute($foo, $bar)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\ParentWithParam;

// ref https://3v4l.org/BPFsU
class ChildMoreParamsNoDefaultValue extends ParentWithParam{
    public function execute($foo, $bar = null)
    {
    }
}

?>
