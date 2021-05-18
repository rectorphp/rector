<?php

declare (strict_types=1);
namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Functional\Parser;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
use RectorPrefix20210518\Symfony\Component\Console\Output\BufferedOutput;
class PrinterTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    /** @var ASTPrinterInterface */
    private $printer;
    public function setUp() : void
    {
        $this->printer = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter(\RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create()->withEmptyLineBreaks()->withSpaceIndentation(4));
    }
    public function dataForPrinterTest()
    {
        $files = \glob(__DIR__ . '/Fixtures/*/*.typoscript');
        $testCases = [];
        foreach ($files as $outputFile) {
            $astFile = \str_replace('.typoscript', '.php', $outputFile);
            /** @noinspection PhpIncludeInspection */
            $ast = (include $astFile);
            $exceptionFile = $outputFile . '.print';
            if (\file_exists($exceptionFile)) {
                $outputFile = $exceptionFile;
            }
            $output = \file_get_contents($outputFile);
            $output = \implode("\n", \explode("\n", $output));
            $testCases[\str_replace(".typoscript", "", \basename($outputFile))] = [$ast, $output];
        }
        return $testCases;
    }
    /**
     * @dataProvider dataForPrinterTest
     *
     * @param $ast
     * @param $expectedOutput
     */
    public function testParsedCodeIsCorrectlyPrinted($ast, $expectedOutput)
    {
        $output = new \RectorPrefix20210518\Symfony\Component\Console\Output\BufferedOutput();
        $this->printer->printStatements($ast, $output);
        $this->assertEquals(\trim($expectedOutput), \trim($output->fetch()));
    }
}
