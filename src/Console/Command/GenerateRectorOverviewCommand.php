<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;
use Rector\Console\Shell;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\RectorDefinition\ConfiguredCodeSample;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use function Safe\ksort;
use function Safe\sprintf;

final class GenerateRectorOverviewCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MarkdownDifferAndFormatter
     */
    private $markdownDifferAndFormatter;

    public function __construct(SymfonyStyle $symfonyStyle, MarkdownDifferAndFormatter $markdownDifferAndFormatter)
    {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->markdownDifferAndFormatter = $markdownDifferAndFormatter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Generates markdown documentation of all Rectors.');
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

        $rectorsByGroup = $this->groupRectors($this->getProjectsRectors());
        $this->printRectorsByGroup($rectorsByGroup);

        $this->symfonyStyle->writeln('---');

        $this->symfonyStyle->writeln('## General');
        $this->symfonyStyle->newLine();

        $rectorsByGroup = $this->groupRectors($this->getGeneralRectors());
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
     * @return RectorInterface[]
     */
    private function getProjectsRectors(): array
    {
        return $this->getRectorsFromDirectory(
            [__DIR__ . '/../../../packages'],
            [__DIR__ . '/../../../packages/YamlRector']
        );
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

    /**
     * @return RectorInterface[]
     */
    private function getGeneralRectors(): array
    {
        return $this->getRectorsFromDirectory([__DIR__ . '/../../../src'], [__DIR__ . '/../../../packages/YamlRector']);
    }

    private function detectGroupFromRectorClass(string $rectorClass): string
    {
        $rectorClassParts = explode('\\', $rectorClass);

        // basic Rectors
        if (Strings::startsWith($rectorClass, 'Rector\Rector\\')) {
            return $rectorClassParts[count($rectorClassParts) - 2];
        }

        // Yaml
        if (Strings::startsWith($rectorClass, 'Rector\YamlRector\\')) {
            return 'Yaml';
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
     * @param string[] $directories
     * @param string[] $directoriesToExclude
     * @return RectorInterface[]
     */
    private function getRectorsFromDirectory(array $directories, array $directoriesToExclude = []): array
    {
        $robotLoader = new RobotLoader();

        foreach ($directories as $directory) {
            $robotLoader->addDirectory($directory);
        }
        foreach ($directoriesToExclude as $directoryToExclude) {
            $robotLoader->excludeDirectory($directoryToExclude);
        }

        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_rector_finder');
        $robotLoader->acceptFiles = ['*Rector.php'];
        $robotLoader->rebuild();

        $rectors = [];
        foreach (array_keys($robotLoader->getIndexedClasses()) as $class) {
            $reflectionClass = new ReflectionClass($class);
            if ($reflectionClass->isAbstract()) {
                continue;
            }

            $rector = $reflectionClass->newInstanceWithoutConstructor();
            if (! $rector instanceof RectorInterface) {
                throw new ShouldNotHappenException(sprintf(
                    '"%s" found something that looks like Rector but does not implements "%s" interface.',
                    __METHOD__,
                    RectorInterface::class
                ));
            }

            $rectors[] = $rector;
        }

        return $rectors;
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
