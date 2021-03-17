<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class SkipInvalidProperty
{
    /**
     * @var invalid
     */
    private $cantTouchThis;
}
