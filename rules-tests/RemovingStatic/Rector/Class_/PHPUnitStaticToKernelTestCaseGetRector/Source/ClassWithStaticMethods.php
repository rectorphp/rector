<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source;

final class ClassWithStaticMethods
{
    public static function create($value)
    {
        return $value;
    }
}
