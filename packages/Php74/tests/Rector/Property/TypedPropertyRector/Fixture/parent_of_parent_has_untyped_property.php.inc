<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

abstract class Middle extends SomeParent
{
}

final class Child2 extends Middle
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
