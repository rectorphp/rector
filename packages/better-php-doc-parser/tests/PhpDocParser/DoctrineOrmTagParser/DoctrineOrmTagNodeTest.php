<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

final class DoctrineOrmTagNodeTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideDataForTestProperty()
     */
    public function testProperty(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, Property::class);
    }

    public function provideDataForTestProperty()
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/Property');
    }

    /**
     * @dataProvider provideDataForTestClass()
     */
    public function testClass(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, Class_::class);
    }

    public function provideDataForTestClass()
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/Class_');
    }

    /**
     * @dataProvider provideDataForClassMethod()
     */
    public function testClassMethod(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, ClassMethod::class);
    }

    public function provideDataForClassMethod(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/ClassMethod');
    }
}
