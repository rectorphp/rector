<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeChildOfSomeParent;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent;

final class SkipPublicProperty
{
    /**
     * @var SomeChildOfSomeParent
     */
    public $someChildOfSomeParent;
}
