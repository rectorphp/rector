<?php declare(strict_types=1);

namespace Rector\ContributorTools\Command;

use Nette\Utils\Strings;
use Rector\Console\Command\AbstractCommand;
use Rector\Console\Shell;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\ContributorTools\Exception\Command\ContributorCommandInterface;
use Rector\ContributorTools\Finder\RectorsFinder;
use Rector\Exception\ShouldNotHappenException;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class GenerateDocsCommand extends AbstractCommand implements ContributorCommandInterface
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
     * @var RectorsFinder
     */
    private $rectorsFinder;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        MarkdownDifferAndFormatter $markdownDifferAndFormatter,
        RectorsFinder $rectorsFinder
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->markdownDifferAndFormatter = $markdownDifferAndFormatter;
        $this->rectorsFinder = $rectorsFinder;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Generates markdown documentation of all Rectors');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle->writeln('# All Rectors Overview');
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->writeln('- [Projects](#projects)');
        $this->symfonyStyle->writeln('- [General](#general)');
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->writeln('## Projects');
        $this->symfonyStyle->newLine();

        $packageRectors = $this->rectorsFinder->findInDirectory(__DIR__ . '/../../../../packages');
        $rectorsByGroup = $this->groupRectors($packageRectors);
        $this->printRectorsByGroup($rectorsByGroup);

        $this->symfonyStyle->writeln('---');

        $this->symfonyStyle->writeln('## General');
        $this->symfonyStyle->newLine();

        $generalRectors = $this->rectorsFinder->findInDirectory(__DIR__ . '/../../../../src');
        $rectorsByGroup = $this->groupRectors($generalRectors);
        $this->printRectorsByGroup($rectorsByGroup);

        return Shell::CODE_SUCCESS;
    }

    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[][]
     */
    private function groupRectors(array $rectors): array
    {
        $rectorsByGroup = [];
        foreach ($rectors as $rector) {
            $rectorGroup = $this->detectGroupFromRectorClass(get_class($rector));
            $rectorsByGroup[$rectorGroup][] = $rector;
        }

        // sort groups by name to make them more readable
        ksort($rectorsByGroup);

        return $rectorsByGroup;
    }

    /**
     * @param RectorInterface[][] $rectorsByGroup
     */
    private function printRectorsByGroup(array $rectorsByGroup): void
    {
        $this->printGroupsMenu($rectorsByGroup);

        foreach ($rectorsByGroup as $group => $rectors) {
            $this->symfonyStyle->writeln('## ' . $group);
            $this->symfonyStyle->newLine();

            foreach ($rectors as $rector) {
                $this->printRector($rector);
            }
        }
    }

    private function detectGroupFromRectorClass(string $rectorClass): string
    {
        $rectorClassParts = explode('\\', $rectorClass);

        // basic Rectors
        if (Strings::startsWith($rectorClass, 'Rector\Rector\\')) {
            return $rectorClassParts[count($rectorClassParts) - 2];
        }

        // Rector/<PackageGroup>/Rector/SomeRector
        if (count($rectorClassParts) === 4) {
            return $rectorClassParts[1];
        }

        // Rector/<PackageGroup>/Rector/<PackageSubGroup>/SomeRector
        if (count($rectorClassParts) === 5) {
            return $rectorClassParts[1] . '\\' . $rectorClassParts[3];
        }

        throw new ShouldNotHappenException(sprintf(
            'Failed to resolve group from Rector class. Implement a new one in %s',
            __METHOD__
        ));
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

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln(sprintf('- class: `%s`', get_class($rector)));

        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription()) {
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln($rectorDefinition->getDescription());
        }

        foreach ($rectorDefinition->getCodeSamples() as $codeSample) {
            $this->symfonyStyle->newLine();

            if ($codeSample instanceof ConfiguredCodeSample) {
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

            $diff = $this->markdownDifferAndFormatter->bareDiffAndFormatWithoutColors(
                $codeSample->getCodeBefore(),
                $codeSample->getCodeAfter()
            );
            $this->printCodeWrapped($diff, 'diff');
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
}
