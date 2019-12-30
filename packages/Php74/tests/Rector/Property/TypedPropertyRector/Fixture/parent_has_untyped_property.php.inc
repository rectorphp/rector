<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

final class Child extends SomeParent
{
    /**
     * @var string
     */
    protected $name = 'child';

    /**
     * @var string
     */
    protected $typedName = 'child';
}
