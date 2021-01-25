<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

final class SkipParentFromVendor extends Middle
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


abstract class Middle extends SomeParent
{
}

