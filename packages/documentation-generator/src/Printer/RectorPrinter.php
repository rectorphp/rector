<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Printer;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\DocumentationGenerator\PhpKeywordHighlighter;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;
use ReflectionClass;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\DocumentationGenerator\Tests\Printer\RectorPrinter\RectorPrinterTest
 */
final class RectorPrinter
{
    /**
     * @var TestClassResolver
     */
    private $testClassResolver;

    /**
     * @var CodeSamplePrinter
     */
    private $rectorCodeSamplePrinter;

    /**
     * @var PhpKeywordHighlighter
     */
    private $phpKeywordHighlighter;

    public function __construct(
        TestClassResolver $testClassResolver,
        CodeSamplePrinter $rectorCodeSamplePrinter,
        PhpKeywordHighlighter $phpKeywordHighlighter
    ) {
        $this->testClassResolver = $testClassResolver;
        $this->rectorCodeSamplePrinter = $rectorCodeSamplePrinter;
        $this->phpKeywordHighlighter = $phpKeywordHighlighter;
    }

    public function printRector(RectorInterface $rector, bool $isRectorProject): string
    {
        $content = '';
        $headline = $this->getRectorClassWithoutNamespace($rector);

        if ($isRectorProject) {
            $content .= sprintf('### `%s`', $headline) . PHP_EOL;
        } else {
            $content .= sprintf('## `%s`', $headline) . PHP_EOL;
        }

        $rectorClass = get_class($rector);


        $rectorDefinition = $rector->getDefinition();

        $description = $rectorDefinition->getDescription();
        $codeHighlightedDescription = $this->phpKeywordHighlighter->highlight($description);

        $content .= $codeHighlightedDescription . PHP_EOL . PHP_EOL;

        $content .= $this->rectorCodeSamplePrinter->printCodeSamples($rectorDefinition, $rector);
        $content .= PHP_EOL . '<br><br>' . PHP_EOL;

        return $content;
    }
}
