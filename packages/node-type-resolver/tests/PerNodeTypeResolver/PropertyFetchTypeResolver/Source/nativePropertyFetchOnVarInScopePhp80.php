<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

function nativePropertyFetchOnVarInScopePhp80($props): void
{
    if(!$props instanceof ClassWithNativePropsPhp80) {
        return;
    }

    $props->explicitMixed->xxx();
    $props->abcOrString->xxx();
}

