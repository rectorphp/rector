<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class SkipInvalidProperty
{
    /**
     * @var invalid
     */
    private $cantTouchThis;
}
