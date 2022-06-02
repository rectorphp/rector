<?php

namespace Rector\Tests\Php80\Rector\Class_\ConstantListClassToEnumRector\Fixture;

final class SkipNonPublicConst
{
    public const LEFT = 'left';

    protected const RIGHT = 5;
}
