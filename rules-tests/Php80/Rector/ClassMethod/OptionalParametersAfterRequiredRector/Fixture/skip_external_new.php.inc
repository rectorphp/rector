<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Source\vendor\SomeOutsideClass;

final class SkipExternalNew
{
    public function create()
    {
        return new SomeOutsideClass(1, 5);
    }
}
