<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

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
