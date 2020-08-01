<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\OutputFormatter;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DocumentationGenerator\PhpKeywordHighlighter;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;
use ReflectionClass;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorPrinter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

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
        SymfonyStyle $symfonyStyle,
        TestClassResolver $testClassResolver,
        CodeSamplePrinter $rectorCodeSamplePrinter,
        PhpKeywordHighlighter $phpKeywordHighlighter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->testClassResolver = $testClassResolver;
        $this->rectorCodeSamplePrinter = $rectorCodeSamplePrinter;
        $this->phpKeywordHighlighter = $phpKeywordHighlighter;
    }

    public function printRector(RectorInterface $rector, bool $isRectorProject): void
    {
        $headline = $this->getRectorClassWithoutNamespace($rector);

        if ($isRectorProject) {
            $message = sprintf('### `%s`', $headline);
            $this->symfonyStyle->writeln($message);
        } else {
            $message = sprintf('## `%s`', $headline);
            $this->symfonyStyle->writeln($message);
        }

        $rectorClass = get_class($rector);

        $this->symfonyStyle->newLine();
        $message = sprintf(
            '- class: [`%s`](%s)',
            get_class($rector),
            $this->resolveClassFilePathOnGitHub($rectorClass)
        );
        $this->symfonyStyle->writeln($message);

        $rectorTestClass = $this->testClassResolver->resolveFromClassName($rectorClass);
        if ($rectorTestClass !== null) {
            $fixtureDirectoryPath = $this->resolveFixtureDirectoryPathOnGitHub($rectorTestClass);
            if ($fixtureDirectoryPath !== null) {
                $message = sprintf('- [test fixtures](%s)', $fixtureDirectoryPath);
                $this->symfonyStyle->writeln($message);
            }
        }

        $rectorDefinition = $rector->getDefinition();
        $this->ensureRectorDefinitionExists($rectorDefinition, $rector);

        $this->symfonyStyle->newLine();

        $description = $rectorDefinition->getDescription();
        $codeHighlightedDescription = $this->phpKeywordHighlighter->highlight($description);
        $this->symfonyStyle->writeln($codeHighlightedDescription);

        $this->ensureCodeSampleExists($rectorDefinition, $rector);

        $this->rectorCodeSamplePrinter->printCodeSamples($rectorDefinition, $rector);

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('<br><br>');
        $this->symfonyStyle->newLine();
    }

    private function getRectorClassWithoutNamespace(RectorInterface $rector): string
    {
        $rectorClass = get_class($rector);
        $rectorClassParts = explode('\\', $rectorClass);

        return $rectorClassParts[count($rectorClassParts) - 1];
    }

    private function resolveClassFilePathOnGitHub(string $className): string
    {
        $classRelativePath = $this->getClassRelativePath($className);
        return '/../master/' . $classRelativePath;
    }

    private function resolveFixtureDirectoryPathOnGitHub(string $className): ?string
    {
        $classRelativePath = $this->getClassRelativePath($className);

        $fixtureDirectory = dirname($classRelativePath) . '/Fixture';
        if (is_dir($fixtureDirectory)) {
            return '/../master/' . $fixtureDirectory;
        }

        return null;
    }

    private function getClassRelativePath(string $className): string
    {
        $rectorReflectionClass = new ReflectionClass($className);
        $rectorSmartFileInfo = new SmartFileInfo($rectorReflectionClass->getFileName());

        return $rectorSmartFileInfo->getRelativeFilePathFromCwd();
    }

    private function ensureCodeSampleExists(RectorDefinition $rectorDefinition, RectorInterface $rector): void
    {
        if (count($rectorDefinition->getCodeSamples()) !== 0) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Rector "%s" must have at least one code sample. Complete it in "%s()" method.',
            get_class($rector),
            'getDefinition'
        ));
    }

    private function ensureRectorDefinitionExists(RectorDefinition $rectorDefinition, RectorInterface $rector): void
    {
        if ($rectorDefinition->getDescription() !== '') {
            return;
        }

        $message = sprintf(
            'Rector "%s" is missing description. Complete it in "%s()" method.',
            get_class($rector),
            'getDefinition'
        );
        throw new ShouldNotHappenException($message);
    }
}
