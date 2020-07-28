<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\RectorOrder;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnit\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertSameBoolNullToSpecificMethodRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Covers https://github.com/rectorphp/rector/pull/266#issuecomment-355725764
 */
final class RectorOrderTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        // order matters
        return [
            AssertComparisonToSpecificMethodRector::class => [],
            AssertSameBoolNullToSpecificMethodRector::class => [],
            AssertFalseStrposToContainsRector::class => [],
        ];
    }
}
