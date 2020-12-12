<?php

namespace Rector\Symfony\Tests\Rector\ClassMethod\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector\Fixture;

final class Set-autowire-classname
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

final class Set-autowire-classname
{
    /** @required */
    public function autowireSomeClass()
    {
    }
}

?>
