<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Source\ATrait;

class SkipExternalTraitUsedProperty
{
    use ATrait;

    private $usedProperty = 10;
    private static $usedstaticProperty;
}
