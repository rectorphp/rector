<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Source\vendor;

final class SomeOutsideClass
{
    public function __construct($optional = 1, $required)
    {
    }
}
