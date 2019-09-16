<?php

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class SkipInvalidProperty
{
    /**
     * @var invalid
     */
    private $cantTouchThis;
}
