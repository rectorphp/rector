<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Provider;

use Rector\Set\ValueObject\SetList;

final class SetListProvider
{
    /**
     * @return array<int, string>
     */
    public function provide(): array
    {
        $constants = (new \ReflectionClass(SetList::class))
            ->getConstants();

        return array_keys($constants);
    }
}
