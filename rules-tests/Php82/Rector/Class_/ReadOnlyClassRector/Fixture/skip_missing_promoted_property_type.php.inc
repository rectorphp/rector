<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final class SkipMissingPromotedPropertyType
{
    public function __construct(
        private readonly $promotedProperty
    ) {
    }
}
