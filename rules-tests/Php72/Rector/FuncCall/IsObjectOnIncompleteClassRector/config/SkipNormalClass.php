<?php

declare(strict_types=1);

namespace Rector\Tests\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector\config;

use stdClass;

final class SkipNormalClass
{
    public function run(): void
    {
        $classicObject = new stdClass();
        $isObject = is_object($classicObject);
    }
}
