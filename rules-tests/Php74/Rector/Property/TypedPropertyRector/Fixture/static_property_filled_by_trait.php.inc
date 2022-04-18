<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\FillerTrait;

class StaticPropertyFilledByTrait
{
    use FillerTrait;

    /**
     * @var AnotherClass
     */
    private static $someStaticProperty;
}

?>
