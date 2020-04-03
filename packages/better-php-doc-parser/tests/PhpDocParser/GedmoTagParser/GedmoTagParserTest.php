<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\GedmoTagParser;

use Iterator;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNode\Gedmo\BlameableTagValueNode
 */
final class GedmoTagParserTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, Property::class);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/SomeClassMethod.php'];
    }
}
