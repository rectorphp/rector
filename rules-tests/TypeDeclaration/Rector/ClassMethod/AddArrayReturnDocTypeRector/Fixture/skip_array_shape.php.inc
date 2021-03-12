<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class SkipArrayShape
{
    /** @return array{a: string, b: int, c: float}[] */
    public function X(): array
    {
        return [
            [
                'a' => 'string',
                'b' => 1,
                'c' => 1.0,
            ]
        ];
    }
}
