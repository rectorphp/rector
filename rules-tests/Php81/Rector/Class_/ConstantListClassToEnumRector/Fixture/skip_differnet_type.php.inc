<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

final class SkipDifferentType
{
    public const LEFT = 'left';

    public const RIGHT = 5;
}
