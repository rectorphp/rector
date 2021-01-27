<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

class ClassWithNativePropsPhp80
{
    public mixed $explicitMixed;

    public Abc|string $abcOrString;
}
