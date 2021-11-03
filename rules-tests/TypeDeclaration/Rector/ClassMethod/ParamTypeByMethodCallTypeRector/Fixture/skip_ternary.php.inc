<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

final class SkipTernary
{
    public function run($value)
    {
        is_array($value) ? $this->processArray($value) : $this->processMixed($value);
    }

    private function processArray(array $value)
    {

    }

    private function processMixed($value)
    {

    }
}
