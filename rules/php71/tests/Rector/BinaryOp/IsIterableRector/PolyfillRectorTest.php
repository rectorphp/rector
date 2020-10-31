<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\BinaryOp\IsIterableRector;

use Iterator;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php71\Rector\BinaryOp\IsIterableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PolyfillRectorTest extends AbstractRectorTestCase
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
        return IsIterableRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::ITERABLE_TYPE;
    }
}
