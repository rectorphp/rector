<?php declare (strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Wrong;

use Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    public $toBeProtectedProperty;

    protected $toBePrivateProperty;
}
