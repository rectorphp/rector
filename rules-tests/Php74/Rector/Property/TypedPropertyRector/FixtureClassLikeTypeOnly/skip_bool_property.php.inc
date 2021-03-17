<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureClassLikeTypeOnly;

final class SkipBoolProperty
{
    /**
     * @var bool
     * another comment
     */
    private $isTrue = false;
}
