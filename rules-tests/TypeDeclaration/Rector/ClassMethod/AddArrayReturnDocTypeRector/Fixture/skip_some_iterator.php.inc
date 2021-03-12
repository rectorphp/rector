<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SkipSomeIterator
{
    public function someMethod(): Iterator
    {
        return self::someIterator();
    }

    /**
     * @return \Iterator<array<int, SmartFileInfo>>
     */
    public static function someIterator(): Iterator
    {
        yield [100 => new SmartFileInfo('...')];
    }
}

?>
