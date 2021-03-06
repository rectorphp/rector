<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeFromCallersRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddParamTypeFromCallersRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector::class;
    }
}
