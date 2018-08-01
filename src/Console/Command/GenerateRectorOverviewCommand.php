<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;
use Rector\Console\ConsoleStyle;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\Contract\RectorDefinition\CodeSampleInterface;
use Rector\Exception\ShouldNotHappenException;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class GenerateRectorOverviewCommand extends Command
{
    /**
     * @var ConsoleStyle
     */
    private $consoleStyle;

    /**
     * @var MarkdownDifferAndFormatter
     */
    private $markdownDifferAndFormatter;

    public function __construct(ConsoleStyle $consoleStyle, MarkdownDifferAndFormatter $markdownDifferAndFormatter)
    {
        parent::__construct();

        $this->consoleStyle = $consoleStyle;
        $this->markdownDifferAndFormatter = $markdownDifferAndFormatter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Generates markdown documentation of all Rectors.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->consoleStyle->writeln('# All Rectors Overview');
        $this->consoleStyle->newLine();

        // @todo menu to project + general rectors

        $rectorsByGroup = $this->groupRectors($this->getProjectsRectors());
        $this->printRectorsByGroup($rectorsByGroup);

        $this->consoleStyle->writeln('---');

        $rectorsByGroup = $this->groupRectors($this->getGeneralRectors());
        $this->printRectorsByGroup($rectorsByGroup);

        // success
        return 0;
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
     * @return RectorInterface[]
     */
    private function getGeneralRectors(): array
    {
        return $this->getRectorsFromDirectory([__DIR__ . '/../../../src'], [__DIR__ . '/../../../packages/YamlRector']);
    }

    private function printRector(RectorInterface $rector): void
    {
        $headline = $this->getRectorClassWithoutNamespace($rector);
        $this->consoleStyle->writeln(sprintf('### `%s`', $headline));

        $this->consoleStyle->newLine();
        $this->consoleStyle->writeln(sprintf('- class: `%s`', get_class($rector)));

        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription()) {
            $this->consoleStyle->newLine();
            $this->consoleStyle->writeln($rectorDefinition->getDescription());
        }

        $this->consoleStyle->newLine();
        $this->consoleStyle->writeln('```diff');

        [$codeBefore, $codeAfter] = $this->joinBeforeAndAfter($rectorDefinition->getCodeSamples());
        $diff = $this->markdownDifferAndFormatter->bareDiffAndFormatWithoutColors($codeBefore, $codeAfter);
        $this->consoleStyle->write($diff);

        $this->consoleStyle->newLine();
        $this->consoleStyle->writeln('```');

        $this->consoleStyle->newLine(1);
    }

    /**
     * @param CodeSampleInterface[] $codeSamples
     * @return string[]
     */
    private function joinBeforeAndAfter(array $codeSamples): array
    {
        $separator = PHP_EOL . PHP_EOL;

        $codesBefore = [];
        $codesAfter = [];
        foreach ($codeSamples as $codeSample) {
            $codesBefore[] = $codeSample->getCodeBefore();
            $codesAfter[] = $codeSample->getCodeAfter();
        }

        $codeBefore = implode($separator, $codesBefore);
        $codeAfter = implode($separator, $codesAfter);

        return [$codeBefore, $codeAfter];
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

        return $rectorsByGroup;
    }

    private function detectGroupFromRectorClass(string $rectorClass): string
    {
        $rectorClassParts = explode('\\', $rectorClass);

        // basic Rectors
        if (Strings::match($rectorClass, '#^Rector\\\\Rector\\\\#')) {
            return $rectorClassParts[count($rectorClassParts) - 2];
        }

        // Yaml
        if (Strings::match($rectorClass, '#^Rector\\\\YamlRector\\\\#')) {
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
     * @param RectorInterface[][] $rectorsByGroup
     */
    private function printMenu(array $rectorsByGroup): void
    {
        foreach ($rectorsByGroup as $group => $rectors) {
            $escapedGroup = str_replace('\\', '', $group);
            $escapedGroup = Strings::webalize($escapedGroup, '_');

            $this->consoleStyle->writeln(sprintf('- [%s](#%s)', $group, $escapedGroup));
        }

        $this->consoleStyle->newLine();
    }

    private function getRectorClassWithoutNamespace(RectorInterface $rector): string
    {
        $rectorClass = get_class($rector);
        $rectorClassParts = explode('\\', $rectorClass);

        return $rectorClassParts[count($rectorClassParts) - 1];
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
        foreach ($robotLoader->getIndexedClasses() as $class => $filename) {
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
    private function printRectorsByGroup(array $rectorsByGroup): void
    {
        $this->printMenu($rectorsByGroup);

        foreach ($rectorsByGroup as $group => $rectors) {
            $this->consoleStyle->writeln('## ' . $group);
            $this->consoleStyle->newLine();

            foreach ($rectors as $rector) {
                $this->printRector($rector);
            }
        }
    }
}
