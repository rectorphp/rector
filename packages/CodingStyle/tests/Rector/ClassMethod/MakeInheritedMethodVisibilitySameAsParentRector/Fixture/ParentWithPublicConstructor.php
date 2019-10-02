<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class ParentWithPublicConstructor
{
    public function __construct()
    {
    }
}
