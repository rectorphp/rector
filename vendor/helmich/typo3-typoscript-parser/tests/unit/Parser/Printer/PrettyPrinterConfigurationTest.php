<?php

namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Parser\Printer;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration;
use InvalidArgumentException;
use LogicException;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
final class PrettyPrinterConfigurationTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    public function testTabsIndentationStyle() : void
    {
        $prettyPrinterConfiguration = \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create()->withTabs();
        self::assertSame("\t", $prettyPrinterConfiguration->getIndentation());
    }
    public function testSpacesIndentationStyle() : void
    {
        $prettyPrinterConfiguration = \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create()->withSpaceIndentation(4);
        self::assertSame("    ", $prettyPrinterConfiguration->getIndentation());
    }
    public function testWithGlobalStatement() : void
    {
        $prettyPrinterConfiguration = \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create()->withClosingGlobalStatement();
        self::assertTrue($prettyPrinterConfiguration->shouldAddClosingGlobal());
    }
    public function testWithEmptyLineBreaks() : void
    {
        $prettyPrinterConfiguration = \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create()->withEmptyLineBreaks();
        self::assertTrue($prettyPrinterConfiguration->shouldIncludeEmptyLineBreaks());
    }
}
