<?php declare (strict_types=1);

use Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    private static $toBePublicStaticProperty;
}
