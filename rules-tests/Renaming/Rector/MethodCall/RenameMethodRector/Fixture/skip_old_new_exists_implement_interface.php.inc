<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

use Nette\Utils\Html;
use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\AClass;

class SkipOldNewExistsImplementsInterface
{
    public function run(AClass $aClass)
    {
        $aClass->some_old();
    }
}
