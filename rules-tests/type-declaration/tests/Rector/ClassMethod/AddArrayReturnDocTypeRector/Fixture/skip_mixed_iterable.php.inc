<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class SkipMixedIterable
{
    /**
     * @return iterable<mixed>
     */
    public function someDataProvider(): iterable
    {
        yield [42];
        yield [[42]];
    }
}
