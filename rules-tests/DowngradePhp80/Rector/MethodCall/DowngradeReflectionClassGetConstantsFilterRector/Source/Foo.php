<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\Source;

class Foo
{
    public const A1 = 'A1 Value';
    protected const A2 = 'A2 Value';
    private const A3 = 'A3 Value';
}
