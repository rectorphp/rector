<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\ClassLike\UpdateFileNameByClassNameFileSystemRector;

use Iterator;
use Rector\Restoration\Rector\ClassLike\UpdateFileNameByClassNameFileSystemRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UpdateFileNameByClassNameFileSystemRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestFileInfo($smartFileInfo);

        $this->doTestExtraFile('SkipDifferentClassName.php', __DIR__ . '/Fixture/skip_different_class_name.php.inc');
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return UpdateFileNameByClassNameFileSystemRector::class;
    }
}
