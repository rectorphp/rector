<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\Class_\CommunityTestCaseRector;

use Iterator;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\Class_\CommunityTestCaseRector;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class CommunityTestCaseRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo, AddedFileWithContent $addedFileWithContent): void
    {
        $this->doTestFileInfo($fileInfo);
        $this->assertFileWithContentWasAdded($addedFileWithContent);
    }

    public function provideData(): Iterator
    {
        $smartFileSystem = new SmartFileSystem();

        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/some_class.php.inc'),
            new AddedFileWithContent(
                $this->getFixtureTempDirectory() . '/config/configured_rule.php',
                $smartFileSystem->readFile(__DIR__ . '/Expected/config/configured_rule.php')
            ),
        ];
    }

    protected function getRectorClass(): string
    {
        return CommunityTestCaseRector::class;
    }
}
