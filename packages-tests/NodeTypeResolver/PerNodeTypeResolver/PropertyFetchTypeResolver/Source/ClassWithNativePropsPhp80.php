<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

final class ClassWithNativePropsPhp80
{
    public mixed $explicitMixed;

    public Abc|string $abcOrString;
}
