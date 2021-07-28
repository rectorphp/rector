<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Source;

abstract class SomeControl
{
    public function __construct(string $name)
    {
    }
}
