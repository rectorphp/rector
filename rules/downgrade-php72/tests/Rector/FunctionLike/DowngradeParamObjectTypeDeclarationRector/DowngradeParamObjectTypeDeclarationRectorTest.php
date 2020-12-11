<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;

use Iterator;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeParamObjectTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.2
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
        return DowngradeParamObjectTypeDeclarationRector::class;
    }
}
