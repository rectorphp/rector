<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeParamDeclarationRector;

use Iterator;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeParamDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeIterablePseudoTypeParamDeclarationRectorTest extends AbstractRectorTestCase
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
        return DowngradeIterablePseudoTypeParamDeclarationRector::class;
    }
}
