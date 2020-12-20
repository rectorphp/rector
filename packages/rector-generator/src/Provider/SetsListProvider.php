<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Provider;

use Rector\Set\ValueObject\SetList;
use ReflectionClass;

final class SetsListProvider
{
    /**
     * @return array<int, string>
     */
    public function provide(): array
    {
        $setListReflection = new ReflectionClass(SetList::class);
        $constants = $setListReflection->getConstants();

        return array_keys($constants);
    }
}
