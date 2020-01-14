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
}
