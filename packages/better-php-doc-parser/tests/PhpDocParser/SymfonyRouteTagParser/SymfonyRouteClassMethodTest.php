<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyRouteTagParser;

use Iterator;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode
 */
final class SymfonyRouteClassMethodTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, ClassMethod::class, SymfonyRouteTagValueNode::class);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }
}
