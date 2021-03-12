<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddVoidReturnTypeWhereNoReturnRectorTest extends AbstractRectorTestCase
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
        return AddVoidReturnTypeWhereNoReturnRector::class;
    }
}
