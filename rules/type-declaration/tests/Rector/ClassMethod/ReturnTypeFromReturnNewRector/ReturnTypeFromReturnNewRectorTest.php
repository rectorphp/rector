<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector;

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Symplify\SmartFileSystem\SmartFileInfo;
<<<<<<< HEAD
=======
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes

final class ReturnTypeFromReturnNewRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
<<<<<<< HEAD
<<<<<<< HEAD
    public function test(SmartFileInfo $fileInfo): void
=======
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
    public function test(SmartFileInfo $fileInfo): void
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
    {
        $this->doTestFileInfo($fileInfo);
    }

<<<<<<< HEAD
<<<<<<< HEAD
    public function provideData(): Iterator
=======
    public function provideData(): \Iterator
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
    public function provideData(): Iterator
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
<<<<<<< HEAD
<<<<<<< HEAD
        return ReturnTypeFromReturnNewRector::class;
=======
        return \Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector::class;
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
        return ReturnTypeFromReturnNewRector::class;
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
    }
}
