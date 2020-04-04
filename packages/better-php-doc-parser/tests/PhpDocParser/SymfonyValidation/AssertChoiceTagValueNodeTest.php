<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyValidation;

use Iterator;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode
 */
final class AssertChoiceTagValueNodeTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, Property::class, AssertChoiceTagValueNode::class);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/Choice');
    }
}
