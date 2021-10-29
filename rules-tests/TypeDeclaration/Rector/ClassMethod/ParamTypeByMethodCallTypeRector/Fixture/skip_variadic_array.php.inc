<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

final class SkipVariadicArray
{
    public function run(...$items)
    {
        $this->processVariadic($items);
    }

    private function processVariadic(array $value)
    {
    }
}
