<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

final class SkipConditional
{
    public function run($value)
    {
        if (is_array($value)) {
            $this->processArray($value);
        }
    }

    private function processArray(array $value)
    {
    }
}
