<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\OutputFormatter\DumpRectors;

use Nette\Utils\Strings;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Contract\RectorDefinition\CodeSampleInterface;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;
use Rector\Utils\DocumentationGenerator\RectorMetadataResolver;
use ReflectionClass;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MarkdownDumpRectorsOutputFormatter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MarkdownDifferAndFormatter
     */
    private $markdownDifferAndFormatter;

    /**
     * @var RectorMetadataResolver
     */
    private $rectorMetadataResolver;

    /**
     * @var TestClassResolver
     */
    private $testClassResolver;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        MarkdownDifferAndFormatter $markdownDifferAndFormatter,
        RectorMetadataResolver $rectorMetadataResolver,
        TestClassResolver $testClassResolver
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->markdownDifferAndFormatter = $markdownDifferAndFormatter;
        $this->rectorMetadataResolver = $rectorMetadataResolver;
        $this->testClassResolver = $testClassResolver;
    }

    /**
     * @param RectorInterface[] $genericRectors
     * @param RectorInterface[] $packageRectors
     */
    public function format(array $genericRectors, array $packageRectors): void
    {
        $totalRectorCount = count($genericRectors) + count($packageRectors);

        $this->symfonyStyle->writeln(sprintf('# All %d Rectors Overview', $totalRectorCount));
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->writeln('- [Projects](#projects)');
        $this->symfonyStyle->writeln('- [General](#general)');
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->writeln('## Projects');
        $this->symfonyStyle->newLine();

        $this->printRectors($packageRectors);

        $this->symfonyStyle->writeln('---');

        $this->symfonyStyle->writeln('## General');
        $this->symfonyStyle->newLine();

        $this->printRectors($genericRectors);
    }

    /**
     * @param RectorInterface[] $rectors
     */
    private function printRectors(array $rectors): void
    {
        $groupedRectors = $this->groupRectorsByPackage($rectors);
        $this->printGroupsMenu($groupedRectors);

        foreach ($groupedRectors as $group => $rectors) {
            $this->symfonyStyle->writeln('## ' . $group);
            $this->symfonyStyle->newLine();

            foreach ($rectors as $rector) {
                $this->printRector($rector);
            }
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
            $package = $this->rectorMetadataResolver->resolvePackageFromRectorClass(get_class($rector));
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
        foreach (array_keys($rectorsByGroup) as $group) {
            $escapedGroup = str_replace('\\', '', $group);
            $escapedGroup = Strings::webalize($escapedGroup, '_');

            $this->symfonyStyle->writeln(sprintf('- [%s](#%s)', $group, $escapedGroup));
        }

        $this->symfonyStyle->newLine();
    }

    private function printRector(RectorInterface $rector): void
    {
        $headline = $this->getRectorClassWithoutNamespace($rector);
        $this->symfonyStyle->writeln(sprintf('### `%s`', $headline));

        $rectorClass = get_class($rector);

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln(sprintf(
            '- class: [`%s`](%s)',
            get_class($rector),
            $this->resolveClassFilePathOnGitHub($rectorClass)
        ));

        $rectorTestClass = $this->testClassResolver->resolveFromClassName($rectorClass);
        if ($rectorTestClass !== null) {
            $fixtureDirectoryPath = $this->resolveFixtureDirectoryPathOnGitHub($rectorTestClass);
            if ($fixtureDirectoryPath !== null) {
                $this->symfonyStyle->writeln(sprintf('- [test fixtures](%s)', $fixtureDirectoryPath));
            }
        }

        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription() !== '') {
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln($rectorDefinition->getDescription());
        }

        foreach ($rectorDefinition->getCodeSamples() as $codeSample) {
            $this->symfonyStyle->newLine();

            $this->printConfiguration($rector, $codeSample);
            $this->printCodeSample($codeSample);
        }

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('<br>');
        $this->symfonyStyle->newLine();
    }

    private function getRectorClassWithoutNamespace(RectorInterface $rector): string
    {
        $rectorClass = get_class($rector);
        $rectorClassParts = explode('\\', $rectorClass);

        return $rectorClassParts[count($rectorClassParts) - 1];
    }

    private function printConfiguration(RectorInterface $rector, CodeSampleInterface $codeSample): void
    {
        if (! $codeSample instanceof ConfiguredCodeSample) {
            return;
        }

        $configuration = [
            'services' => [
                get_class($rector) => $codeSample->getConfiguration(),
            ],
        ];
        $configuration = Yaml::dump($configuration, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $this->printCodeWrapped($configuration, 'yaml');

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('↓');
        $this->symfonyStyle->newLine();
    }

    private function printCodeSample(CodeSampleInterface $codeSample): void
    {
        $diff = $this->markdownDifferAndFormatter->bareDiffAndFormatWithoutColors(
            $codeSample->getCodeBefore(),
            $codeSample->getCodeAfter()
        );

        $this->printCodeWrapped($diff, 'diff');

        $extraFileContent = $codeSample->getExtraFileContent();
        if ($extraFileContent !== null) {
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln('**New file**');
            $this->symfonyStyle->newLine();
            $this->printCodeWrapped($extraFileContent, 'php');
        }
    }

    private function printCodeWrapped(string $content, string $format): void
    {
        $this->symfonyStyle->writeln(sprintf('```%s%s%s%s```', $format, PHP_EOL, rtrim($content), PHP_EOL));
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
}
