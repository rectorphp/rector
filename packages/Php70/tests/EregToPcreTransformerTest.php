<?php

declare(strict_types=1);

namespace Rector\Php70\Tests;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\Php70\EregToPcreTransformer;

final class EregToPcreTransformerTest extends TestCase
{
    /**
     * @var EregToPcreTransformer
     */
    private $eregToPcreTransformer;

    protected function setUp(): void
    {
        $this->eregToPcreTransformer = new EregToPcreTransformer();
    }

    /**
     * @dataProvider provideDataDropping()
     * @dataProvider provideDataCaseSensitive()
     */
    public function testCaseSensitive(string $ereg, string $expectedPcre): void
    {
        $pcre = $this->eregToPcreTransformer->transform($ereg, false);
        $this->assertSame($expectedPcre, $pcre);
    }

    public function provideDataCaseSensitive(): Iterator
    {
        yield ['hi', '#hi#m'];
    }

    /**
     * @dataProvider provideDataCaseInsensitive()
     */
    public function testCaseInsensitive(string $ereg, string $expectedPcre): void
    {
        $pcre = $this->eregToPcreTransformer->transform($ereg, true);
        $this->assertSame($expectedPcre, $pcre);
    }

    public function provideDataCaseInsensitive(): Iterator
    {
        yield ['hi', '#hi#mi'];
    }

    public function provideDataDropping(): Iterator
    {
        yield ['mearie\.org', '#mearie\.org#m'];
        yield ['mearie[.,]org', '#mearie[\.,]org#m'];
        yield ['[a-z]+[.,][a-z]+', '#[a-z]+[\.,][a-z]+#m'];
        yield ['^[a-z]+[.,][a-z]+$', '#^[a-z]+[\.,][a-z]+$#m'];
        yield ['^[a-z]+[.,][a-z]{3,}$', '#^[a-z]+[\.,][a-z]{3,}$#m'];
        yield ['a|b|(c|d)|e', '#a|b|(c|d)|e#m'];
        yield ['a|b|()|c', '#a|b|()|c#m'];
        yield ['[[:alnum:][:punct:]]', '#[[:alnum:][:punct:]]#m'];
        yield ['[]-z]', '#[\]-z]#m'];
        yield ['[[a]]', '#[\[a]\]#m'];
        yield ['[---]', '#[\--\-]#m'];
        yield ['[a\z]', '#[a\\\z]#m'];
        yield ['[^^]', '#[^\^]#m'];
        yield ['^$^$^$^$', '#^$^$^$^$#m'];
        yield ['\([^>]*\"?[^)]*\)', '#\([^>]*"?[^\)]*\)#m'];
        yield [
            '^(http(s?):\/\/|ftp:\/\/)*([[:alpha:]][-[:alnum:]]*[[:alnum:]])(\.[[:alpha:]][-[:alnum:]]*[[:alpha:]])+(/[[:alpha:]][-[:alnum:]]*[[:alnum:]])*(\/?)(/[[:alpha:]][-[:alnum:]]*\.[[:alpha:]]{3,5})?(\?([[:alnum:]][-_%[:alnum:]]*=[-_%[:alnum:]]+)(&([[:alnum:]][-_%[:alnum:]]*=[-_%[:alnum:]]+))*)?$',
            '#^(http(s?):\/\/|ftp:\/\/)*([[:alpha:]][\-[:alnum:]]*[[:alnum:]])(\.[[:alpha:]][\-[:alnum:]]*[[:alpha:]])+(\/[[:alpha:]][\-[:alnum:]]*[[:alnum:]])*(\/?)(\/[[:alpha:]][\-[:alnum:]]*\.[[:alpha:]]{3,5})?(\?([[:alnum:]][\-_%[:alnum:]]*=[\-_%[:alnum:]]+)(&([[:alnum:]][\-_%[:alnum:]]*=[\-_%[:alnum:]]+))*)?$#m',
        ];
    }
}
