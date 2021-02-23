<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector;

use Iterator;
use Rector\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ExtraFilesTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     * @param SmartFileInfo[] $extraFileInfos
     */
    public function test(SmartFileInfo $originalFileInfo, array $extraFileInfos = []): void
    {
        $this->doTestFileInfo($originalFileInfo, $extraFileInfos);
    }

    public function provideData(): Iterator
    {
        $extraFileInfos = [new SmartFileInfo(__DIR__ . '/Source/UseAbstract.php')];
        yield [new SmartFileInfo(__DIR__ . '/FixtureExtraFiles/SkipUsedAbstractClass.php.inc'), $extraFileInfos];

        $extraFileInfos = [
            new SmartFileInfo(__DIR__ . '/Source/AbstractMain.php'),
            new SmartFileInfo(__DIR__ . '/Source/AbstractChild.php'),
        ];
        yield [new SmartFileInfo(__DIR__ . '/FixtureExtraFiles/ExtendsAbstractChild.php.inc'), $extraFileInfos];
    }

    protected function getRectorClass(): string
    {
        return RemoveEmptyAbstractClassRector::class;
    }
}
