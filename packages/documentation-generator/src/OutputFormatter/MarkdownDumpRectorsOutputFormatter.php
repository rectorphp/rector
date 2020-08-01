<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\OutputFormatter;

use Nette\Utils\Strings;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DocumentationGenerator\PhpKeywordHighlighter;
use Rector\DocumentationGenerator\RectorMetadataResolver;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;
use ReflectionClass;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MarkdownDumpRectorsOutputFormatter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var RectorMetadataResolver
     */
    private $rectorMetadataResolver;

    /**
     * @var TestClassResolver
     */
    private $testClassResolver;

    /**
     * @var PhpKeywordHighlighter
     */
    private $phpKeywordHighlighter;

    /**
     * @var RectorCodeSamplePrinter
     */
    private $rectorCodeSamplePrinter;

    public function __construct(
        PhpKeywordHighlighter $phpKeywordHighlighter,
        RectorMetadataResolver $rectorMetadataResolver,
        SymfonyStyle $symfonyStyle,
        TestClassResolver $testClassResolver,
        RectorCodeSamplePrinter $rectorCodeSamplePrinter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectorMetadataResolver = $rectorMetadataResolver;
        $this->testClassResolver = $testClassResolver;
        $this->phpKeywordHighlighter = $phpKeywordHighlighter;
        $this->rectorCodeSamplePrinter = $rectorCodeSamplePrinter;
    }

    /**
     * @param RectorInterface[] $packageRectors
     */
    public function format(array $packageRectors, bool $isRectorProject): void
    {
        $totalRectorCount = count($packageRectors);
        $message = sprintf('# All %d Rectors Overview', $totalRectorCount);

        $this->symfonyStyle->writeln($message);
        $this->symfonyStyle->newLine();

        if ($isRectorProject) {
            $this->symfonyStyle->writeln('- [Projects](#projects)');
            $this->printRectorsWithHeadline($packageRectors, 'Projects');
        } else {
            $this->printRectors($packageRectors, $isRectorProject);
        }
    }

    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[][]
     */
    private function groupRectorsByPackage(array $rectors): array
    {
        $rectorsByPackage = [];
        foreach ($rectors as $rector) {
            $rectorClass = get_class($rector);
            $package = $this->rectorMetadataResolver->resolvePackageFromRectorClass($rectorClass);
            $rectorsByPackage[$package][] = $rector;
        }

        // sort groups by name to make them more readable
        ksort($rectorsByPackage);

        return $rectorsByPackage;
    }

    /**
     * @param RectorInterface[][] $rectorsByGroup
     */
    private function printGroupsMenu(array $rectorsByGroup): void
    {
        foreach ($rectorsByGroup as $group => $rectors) {
            $escapedGroup = str_replace('\\', '', $group);
            $escapedGroup = Strings::webalize($escapedGroup, '_');
            $message = sprintf('- [%s](#%s) (%d)', $group, $escapedGroup, count($rectors));

            $this->symfonyStyle->writeln($message);
        }

        $this->symfonyStyle->newLine();
    }

    private function printRector(RectorInterface $rector, bool $isRectorProject): void
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

    /**
     * @param RectorInterface[] $rectors
     */
    private function printRectorsWithHeadline(array $rectors, string $headline): void
    {
        if (count($rectors) === 0) {
            return;
        }

        $this->symfonyStyle->writeln('---');
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->writeln('## ' . $headline);
        $this->symfonyStyle->newLine();

        $this->printRectors($rectors, true);
    }

    /**
     * @param RectorInterface[] $rectors
     */
    private function printRectors(array $rectors, bool $isRectorProject): void
    {
        $groupedRectors = $this->groupRectorsByPackage($rectors);

        if ($isRectorProject) {
            $this->printGroupsMenu($groupedRectors);
        }

        foreach ($groupedRectors as $group => $rectors) {
            if ($isRectorProject) {
                $this->symfonyStyle->writeln('## ' . $group);
                $this->symfonyStyle->newLine();
            }

            foreach ($rectors as $rector) {
                $this->printRector($rector, $isRectorProject);
            }
        }
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
}
