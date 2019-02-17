<?php declare(strict_types=1);

namespace Rector\ContributorTools\OutputFormatter;

use Nette\Utils\Strings;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\Contract\RectorDefinition\CodeSampleInterface;
use Rector\ContributorTools\Contract\OutputFormatterInterface;
use Rector\ContributorTools\RectorMetadataResolver;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

final class MarkdownOutputFormatter implements OutputFormatterInterface
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

    public function __construct(
        SymfonyStyle $symfonyStyle,
        MarkdownDifferAndFormatter $markdownDifferAndFormatter,
        RectorMetadataResolver $rectorMetadataResolver
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->markdownDifferAndFormatter = $markdownDifferAndFormatter;
        $this->rectorMetadataResolver = $rectorMetadataResolver;
    }

    public function getName(): string
    {
        return 'markdown';
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

    private function printRector(RectorInterface $rector): void
    {
        $headline = $this->getRectorClassWithoutNamespace($rector);
        $this->symfonyStyle->writeln(sprintf('### `%s`', $headline));

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln(sprintf('- class: `%s`', get_class($rector)));

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

    private function printCodeWrapped(string $content, string $format): void
    {
        $this->symfonyStyle->writeln(sprintf('```%s%s%s%s```', $format, PHP_EOL, rtrim($content), PHP_EOL));
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
    }
}
