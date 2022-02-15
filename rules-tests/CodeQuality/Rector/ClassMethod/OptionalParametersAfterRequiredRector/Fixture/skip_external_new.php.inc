<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

use Rector\Tests\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Source\vendor\SomeOutsideClass;

final class SkipExternalNew
{
    public function create()
    {
        return new SomeOutsideClass(1, 5);
    }
}
