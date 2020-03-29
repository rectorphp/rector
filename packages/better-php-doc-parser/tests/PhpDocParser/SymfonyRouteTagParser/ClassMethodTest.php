<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyRouteTagParser;

use Iterator;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\OrmTagParser\AbstractPhpDocInfoTest;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode
 */
final class ClassMethodTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath, string $expectedPrintedPhpDoc): void
    {
        // this is needed to have "Symfony\Component\Routing\Annotation\Route" in the annotation
        $classMethod = $this->parseFileAndGetFirstNodeOfType($filePath, ClassMethod::class);

        $printedPhpDocInfo = $this->printNodePhpDocInfoToString($classMethod);

        $this->assertStringEqualsFile($expectedPrintedPhpDoc, $printedPhpDocInfo);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/SomeClassMethod.php', __DIR__ . '/Fixture/expected_some_class_method.txt'];
    }
}
