<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\GedmoTagParser;

use Iterator;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Gedmo\BlameableTagValueNode
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SlugTagValueNode
 */
final class GedmoTagParserTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     * @param class-string[] $expectedTagValueNodeTypes
     *
     */
    public function test(string $filePath, array $expectedTagValueNodeTypes): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, Property::class);

        $this->doTestContainsTag($filePath, $expectedTagValueNodeTypes);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture', '*.php');
    }
}
