<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\Something;

class SkipDifferentType extends AbstractType
{
    public function anyMethod(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();
    }
}
