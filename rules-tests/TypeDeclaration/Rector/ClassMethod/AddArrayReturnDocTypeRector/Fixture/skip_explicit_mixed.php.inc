<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class SkipExplicitMixed
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
