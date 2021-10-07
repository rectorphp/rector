<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\SimpleObject;

final class SkipEmptyObject
{
    public function run(SimpleObject $simpleObject)
    {
        return $simpleObject->name;
    }
}
