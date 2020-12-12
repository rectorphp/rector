<?php

namespace Rector\Symfony\Tests\Rector\ClassMethod\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector\Fixture;

final class SomeClass
{
    /** @required */
    public function foo()
    {
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\ClassMethod\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector\Fixture;

final class SomeClass
{
    /** @required */
    public function autowireSomeClass()
    {
    }
}

?>
