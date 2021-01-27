<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

function nativePropertyFetchOnTypedVarPhp80(ClassWithNativePropsPhp80 $props): void
{
    $props->explicitMixed->xxx();
    $props->abcOrString->xxx();
}

