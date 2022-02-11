<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class SkipNullFalseMixed
{
    /**
     * @var null|false|mixed
     */
    private $unionValue;
}
