<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

class NativePropertyFetchExample
{
    public function example(ClassWithNativeProps $props): void
    {
        $props->implicitMixed->xxx();
        $props->explicitMixed->xxx();
        $props->text->xxx();
        $props->number->xxx();
        $props->textNullable->xxx();
        $props->numberNullable->xxx();
        $props->abc->xxx();
        $props->abcNullable->xxx();
        $props->abcFQ->xxx();
        $props->nonexistent->xxx();
        $props->nonexistentFQ->xxx();
        $props->array->xxx();
        $props->arrayOfAbcs->xxx();
    }
}
