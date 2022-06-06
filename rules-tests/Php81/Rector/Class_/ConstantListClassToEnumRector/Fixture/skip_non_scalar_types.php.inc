<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

final class SkipNonScalarTypes
{
    public const LEFT = self::class;

    public const RIGHT = self::class;
}
