<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class PrivateClassParent extends ParentClassWithPrivateMethod
{
    public function run()
    {
    }
}

class ParentClassWithPrivateMethod
{
    private function run()
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class PrivateClassParent extends ParentClassWithPrivateMethod
{
    private function run()
    {
    }
}

class ParentClassWithPrivateMethod
{
    private function run()
    {
    }
}

?>
