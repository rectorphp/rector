<?php

namespace Rector\Symfony\Tests\Rector\ClassMethod\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector\Fixture;

final class SkipSetAutowireClassname
{
    /** @required */
    public function autowireSomeClass2()
    {
    }
}

?>
