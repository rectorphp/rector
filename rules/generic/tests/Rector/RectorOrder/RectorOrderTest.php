<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\RectorOrder;

use Iterator;
use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return array<string, mixed[]>
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
