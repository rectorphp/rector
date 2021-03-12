<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

use Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Source\SomeOutsideClass;

final class SkipExternalNew
{
    public function create()
    {
        return new SomeOutsideClass(1, 5);
    }
}
