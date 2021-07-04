<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class SkipVoid
{
    /**
     * @var void
     */
    private $shouldBeSkipped;
}
