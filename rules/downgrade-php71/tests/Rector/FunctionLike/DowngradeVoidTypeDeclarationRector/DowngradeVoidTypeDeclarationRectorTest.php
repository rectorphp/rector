<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;

use Iterator;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeVoidTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
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
        return DowngradeVoidTypeDeclarationRector::class;
    }
}
