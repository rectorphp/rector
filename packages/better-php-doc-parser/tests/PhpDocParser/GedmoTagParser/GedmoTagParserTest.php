<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\GedmoTagParser;

use Iterator;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\BlameableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Gedmo\BlameableTagValueNode
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SlugTagValueNode
 */
final class GedmoTagParserTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideDataForBlameable()
     */
    public function testBlameable(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, BlameableTagValueNode::class);
    }

    public function provideDataForBlameable(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/Blameable');
    }

    /**
     * @dataProvider provideDataForBlameable()
     */
    public function testSlug(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, SlugTagValueNode::class);
    }

    public function provideDataForSlug(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/Slug');
    }
}
