<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class SkipNullFalseMixedObject
{
    /**
     * @var null|false|mixed|object
     */
    private $unionValue;
}
