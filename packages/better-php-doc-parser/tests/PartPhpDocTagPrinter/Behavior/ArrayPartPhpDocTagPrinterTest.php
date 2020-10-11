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
     * @param mixed[] $items
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
        yield [[
            'strict' => 'yes',
        ], 'option', 'option={"strict":"yes"}'];

        // bool
        yield [[
            'strict' => false,
        ], 'option', 'option={"strict":false}'];

        // multiple items, separated by comma
        yield [
            [
                'less' => 'NO',
                'more' => 'YES',
            ], 'what', 'what={"less":"NO", "more":"YES"}', ];

        // preslash
        yield [['\John'], 'name', 'name={"\John"}'];

        yield [['0', '3023', '3610'], 'choices', 'choices={"0", "3023", "3610"}'];
    }
}
