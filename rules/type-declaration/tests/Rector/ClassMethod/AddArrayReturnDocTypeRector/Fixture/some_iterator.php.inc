<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SomeIterator
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
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SomeIterator
{
    /**
     * @return Iterator<mixed, \Symplify\SmartFileSystem\SmartFileInfo[]>
     */
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
