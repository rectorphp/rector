<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipClosureCallableOverride
{
    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            /** @var mixed $items */
            'flatten_array_count' => function (array $items): int {
                $flattenItems = Arrays::flatten($items);

                return count($flattenItems);
            },
        ];
    }
}
