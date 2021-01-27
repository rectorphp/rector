<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

function nativePropertyFetchOnVarInScope($props): void
{
    if(!$props instanceof ClassWithNativeProps) {
        return;
    }

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
    $props->implicitMixed->xxx();
    $props->thisDoesNotExistOnTheObject->xxx();
}

