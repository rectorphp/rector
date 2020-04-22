<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyValidation;

use Iterator;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode
 */
final class AssertTypeTagValueNodeTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, AssertTypeTagValueNode::class);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/Type');
    }
}
