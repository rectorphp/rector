<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Printer;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\DocumentationGenerator\Guard\PrePrintRectorGuard;
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

    /**
     * @var PrePrintRectorGuard
     */
    private $prePrintRectorGuard;

    public function __construct(
        TestClassResolver $testClassResolver,
        CodeSamplePrinter $rectorCodeSamplePrinter,
        PhpKeywordHighlighter $phpKeywordHighlighter,
        PrePrintRectorGuard $prePrintRectorGuard
    ) {
        $this->testClassResolver = $testClassResolver;
        $this->rectorCodeSamplePrinter = $rectorCodeSamplePrinter;
        $this->phpKeywordHighlighter = $phpKeywordHighlighter;
        $this->prePrintRectorGuard = $prePrintRectorGuard;
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

        $content .= PHP_EOL;
        $content .= $this->createRectorFileLink($rector, $rectorClass) . PHP_EOL;

        $rectorTestClass = $this->testClassResolver->resolveFromClassName($rectorClass);
        if ($rectorTestClass !== null) {
            $fixtureDirectoryPath = $this->resolveFixtureDirectoryPathOnGitHub($rectorTestClass);
            if ($fixtureDirectoryPath !== null) {
                $message = sprintf('- [test fixtures](%s)', $fixtureDirectoryPath);
                $content .= $message . PHP_EOL;
            }
        }

        $this->prePrintRectorGuard->ensureRectorRefinitionHasContent($rector);

        $content .= PHP_EOL;

        $rectorDefinition = $rector->getDefinition();
        $description = $rectorDefinition->getDescription();
        $codeHighlightedDescription = $this->phpKeywordHighlighter->highlight($description);

        $content .= $codeHighlightedDescription . PHP_EOL . PHP_EOL;

        $content .= $this->rectorCodeSamplePrinter->printCodeSamples($rectorDefinition, $rector);
        $content .= PHP_EOL . '<br><br>' . PHP_EOL;

        return $content;
    }

    private function getRectorClassWithoutNamespace(RectorInterface $rector): string
    {
        $rectorClass = get_class($rector);
        $rectorClassParts = explode('\\', $rectorClass);

        return $rectorClassParts[count($rectorClassParts) - 1];
    }

    private function createRectorFileLink(RectorInterface $rector, string $rectorClass): string
    {
        return sprintf(
            '- class: [`%s`](%s)',
            get_class($rector),
            $this->resolveClassFilePathOnGitHub($rectorClass)
        );
    }

    private function resolveFixtureDirectoryPathOnGitHub(string $className): ?string
    {
        $classRelativePath = $this->getClassRelativePath($className);

        $fixtureDirectory = dirname($classRelativePath) . '/Fixture';
        if (is_dir($fixtureDirectory)) {
            return '/' . ltrim($fixtureDirectory, '/');
        }

        return null;
    }

    private function resolveClassFilePathOnGitHub(string $className): string
    {
        $classRelativePath = $this->getClassRelativePath($className);
        return '/' . ltrim($classRelativePath, '/');
    }

    private function getClassRelativePath(string $className): string
    {
        $rectorReflectionClass = new ReflectionClass($className);
        $rectorSmartFileInfo = new SmartFileInfo($rectorReflectionClass->getFileName());

        return $rectorSmartFileInfo->getRelativeFilePathFromCwd();
    }
}
