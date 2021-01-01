<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Provider;

use Rector\Set\ValueObject\SetList;
use ReflectionClass;

final class SetsListProvider
{
    /**
     * @return string[]
     */
    public function provide(): array
    {
        $reflectionClass = new ReflectionClass(SetList::class);
        $constants = $reflectionClass->getConstants();

        return array_keys($constants);
    }
}
