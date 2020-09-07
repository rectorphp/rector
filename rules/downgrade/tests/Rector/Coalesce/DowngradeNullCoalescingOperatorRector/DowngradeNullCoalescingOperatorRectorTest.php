<?php

declare(strict_types=1);

namespace Rector\Downgrade\Tests\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Downgrade\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeNullCoalescingOperatorRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return DowngradeNullCoalescingOperatorRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::BEFORE_NULL_COALESCE_ASSIGN;
    }
}
