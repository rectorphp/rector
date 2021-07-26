<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class A {}
class B {}
class C {}

final class SkipMultipleClassStringArray
{
    /**
     * @var class-string[]
     */
    public $classes = [
        A::class,
        B::class,
        C::class,
    ];
}

?>
