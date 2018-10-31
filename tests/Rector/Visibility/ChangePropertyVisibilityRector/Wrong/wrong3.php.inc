<?php declare (strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Wrong;

class NormalObject
{
    private static $toBePublicStaticProperty;
}
