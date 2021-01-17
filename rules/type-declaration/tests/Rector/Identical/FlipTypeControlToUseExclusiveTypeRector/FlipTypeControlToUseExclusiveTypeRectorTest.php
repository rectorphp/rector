<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;

use Iterator;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FlipTypeControlToUseExclusiveTypeRectorTest extends AbstractRectorTestCase
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
        return FlipTypeControlToUseExclusiveTypeRector::class;
    }
}
