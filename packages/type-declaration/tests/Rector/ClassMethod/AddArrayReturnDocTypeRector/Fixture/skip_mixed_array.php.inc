<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class SkipMixedArray
{
    /**
     * @return mixed[]
     */
    public function someMethod(): array
    {
        return [
            42,
            [42],
        ];
    }
}
