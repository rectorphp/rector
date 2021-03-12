<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\Something;

class SkipDifferentType extends AbstractType
{
    public function anyMethod(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();
    }
}
