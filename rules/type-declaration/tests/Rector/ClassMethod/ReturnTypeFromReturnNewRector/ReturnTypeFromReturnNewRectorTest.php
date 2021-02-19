<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector;

<<<<<<< HEAD
use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Symplify\SmartFileSystem\SmartFileInfo;
=======
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector

final class ReturnTypeFromReturnNewRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
<<<<<<< HEAD
    public function test(SmartFileInfo $fileInfo): void
=======
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
    {
        $this->doTestFileInfo($fileInfo);
    }

<<<<<<< HEAD
    public function provideData(): Iterator
=======
    public function provideData(): \Iterator
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
<<<<<<< HEAD
        return ReturnTypeFromReturnNewRector::class;
=======
        return \Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector::class;
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
    }
}
