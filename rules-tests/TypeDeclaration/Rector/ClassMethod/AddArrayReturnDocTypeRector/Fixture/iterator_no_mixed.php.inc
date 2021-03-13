<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;
use Symplify\SmartFileSystem\SmartFileInfo;

class IteratorNoMixed
{
    public function getFiles(): iterable
    {
        return $this->yieldFilesFromDirectory();
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    private function yieldFilesFromDirectory()
    {
        yield [new SmartFileInfo('...')];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;
use Symplify\SmartFileSystem\SmartFileInfo;

class IteratorNoMixed
{
    /**
     * @return Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
     */
    public function getFiles(): iterable
    {
        return $this->yieldFilesFromDirectory();
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    private function yieldFilesFromDirectory()
    {
        yield [new SmartFileInfo('...')];
    }
}

?>
