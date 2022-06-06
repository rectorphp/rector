<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

final class SkipNonPublicConst
{
    public const LEFT = 'left';

    protected const RIGHT = 5;
}
