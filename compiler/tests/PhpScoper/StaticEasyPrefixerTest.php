<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests\PhpScoper;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;

final class StaticEasyPrefixerTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testUnPrefixQuotedValues(string $unPrefixedValue, string $expected): void
    {
        $unPrefixedValue = StaticEasyPrefixer::unPrefixQuotedValues('Prefix', $unPrefixedValue);
        $this->assertSame($expected, $unPrefixedValue);
    }

    public function provideData(): Iterator
    {
        yield ["'Prefix\\SomeNamespace\\", '\'SomeNamespace\\'];
    }

    /**
     * @dataProvider provideDataForSlashes()
     */
    public function testUnPreslashQuotedValues(string $inputContent, string $expected): void
    {
        $inputContent = StaticEasyPrefixer::unPreSlashQuotedValues($inputContent);
        $this->assertSame($expected, $inputContent);
    }

    public function provideDataForSlashes(): Iterator
    {
        yield ["'\@ORM\Entity", "'@ORM\Entity"];
        yield ["'\\", "'\\"];
    }
}
