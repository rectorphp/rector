<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Source\SomeControl;

final class SkipSetType extends SomeControl
{
    public function __construct(int $name)
    {
        parent::__construct($name);
    }
}
