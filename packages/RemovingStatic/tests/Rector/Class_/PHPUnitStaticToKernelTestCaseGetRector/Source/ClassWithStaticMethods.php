<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source;

final class ClassWithStaticMethods
{
    public static function create($value)
    {
        return $value;
    }
}
