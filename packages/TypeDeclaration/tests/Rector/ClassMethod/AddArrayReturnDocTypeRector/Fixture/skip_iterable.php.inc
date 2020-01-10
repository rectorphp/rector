<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class SkipIterable
{
    /**
     * @return iterable<class-string>
     */
    public function someMethod(): iterable
    {
        yield self::class;
    }

    /**
     * @return iterable<mixed>
     */
    public function someDataProvider(): iterable
    {
        yield [42];
        yield ['42'];
        yield [42.0];
        yield [[42]];
    }
}
