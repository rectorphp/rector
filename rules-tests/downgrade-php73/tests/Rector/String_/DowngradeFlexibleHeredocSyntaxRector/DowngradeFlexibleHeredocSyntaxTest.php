<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Tests\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;

use Iterator;
use Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP >= 7.3
 */
final class DowngradeFlexibleHeredocSyntaxTest extends AbstractRectorTestCase
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
        return DowngradeFlexibleHeredocSyntaxRector::class;
    }
}
