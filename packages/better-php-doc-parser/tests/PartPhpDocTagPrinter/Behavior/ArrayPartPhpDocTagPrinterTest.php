<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PartPhpDocTagPrinter\Behavior;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\BetterPhpDocParser\Tests\PartPhpDocTagPrinter\Behavior\Source\PhpDocTagNodeWithArrayPrinter;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;

/**
 * @see \Rector\BetterPhpDocParser\PartPhpDocTagPrinter\Behavior\ArrayPartPhpDocTagPrinterTrait
 */
final class ArrayPartPhpDocTagPrinterTest extends TestCase
{
    /**
     * @var PhpDocTagNodeWithArrayPrinter
     */
    private $phpDocTagNodeWithArrayPrinter;

    protected function setUp(): void
    {
        $this->phpDocTagNodeWithArrayPrinter = new PhpDocTagNodeWithArrayPrinter();
    }

    /**
     * @dataProvider provideData()
     */
    public function test(array $items, string $key, string $expectedContent): void
    {
        $tagValueNodeConfiguration = new TagValueNodeConfiguration();
        $output = $this->phpDocTagNodeWithArrayPrinter->printArrayItem($items, $key, $tagValueNodeConfiguration);

        $this->assertSame($expectedContent, $output);
    }

    public function provideData(): Iterator
    {
        yield [['strict' => 'yes'], 'option', 'option={"strict":"yes"}'];
        yield [['strict' => false], 'option', 'option={"strict":false}'];
    }
}
