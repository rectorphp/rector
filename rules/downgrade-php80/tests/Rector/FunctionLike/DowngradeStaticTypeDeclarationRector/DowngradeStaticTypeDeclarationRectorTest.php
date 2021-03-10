<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeStaticTypeDeclarationRector;

use Iterator;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeStaticTypeDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeStaticTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 8.0
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo[]>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return DowngradeStaticTypeDeclarationRector::class;
    }
}
