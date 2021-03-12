<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class ChildClass extends ParentClass
{
    public function run()
    {
    }
}

class ParentClass
{
    protected function run()
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class ChildClass extends ParentClass
{
    protected function run()
    {
    }
}

class ParentClass
{
    protected function run()
    {
    }
}

?>
