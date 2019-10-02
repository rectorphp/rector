<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class SomeClass
{
    public function run()
    {
        // use PSR-4 namespace + "SomeClass" for unique class name
        // use "run()" method as convention

        // code before
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class SomeClass
{
    public function run()
    {
        // use PSR-4 namespace + "SomeClass" for unique class name
        // use "run()" method as convention

        // code after
    }
}

?>
