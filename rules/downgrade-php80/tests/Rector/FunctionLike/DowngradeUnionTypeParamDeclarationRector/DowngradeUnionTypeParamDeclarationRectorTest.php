<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector;

use Iterator;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeUnionTypeParamDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     * @requires PHP 8.0
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
        return DowngradeUnionTypeParamDeclarationRector::class;
    }
}
