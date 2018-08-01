<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Loaders\RobotLoader;
use Rector\Console\ConsoleStyle;
use Rector\Console\Output\DescribeCommandReporter;
use Rector\Contract\Rector\RectorInterface;
use Rector\Guard\RectorGuard;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\YamlRector\YamlFileProcessor;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class DescribeCommand extends Command
{
    /**
     * @var string
     */
    public const FORMAT_CLI = 'cli';

    /**
     * @var string
     */
    public const FORMAT_MARKDOWN = 'md';

    /**
     * @var string
     */
    private const OPTION_ALL = 'all';

    /**
     * @var string
     */
    private const OPTION_FORMAT = 'format';

    /**
     * @var ConsoleStyle
     */
    private $consoleStyle;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var DescribeCommandReporter
     */
    private $describeCommandReporter;

    /**
     * @var RectorGuard
     */
    private $rectorGuard;

    /**
     * @var YamlFileProcessor
     */
    private $yamlFileProcessor;

    public function __construct(
        ConsoleStyle $consoleStyle,
        RectorNodeTraverser $rectorNodeTraverser,
        DescribeCommandReporter $describeCommandReporter,
        RectorGuard $rectorGuard,
        YamlFileProcessor $yamlFileProcessor
    ) {
        parent::__construct();

        $this->consoleStyle = $consoleStyle;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->describeCommandReporter = $describeCommandReporter;
        $this->rectorGuard = $rectorGuard;
        $this->yamlFileProcessor = $yamlFileProcessor;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Shows detailed description of loaded Rectors.');
        $this->addOption(self::OPTION_FORMAT, null, InputOption::VALUE_REQUIRED, 'Output format.', self::FORMAT_CLI);
        $this->addOption(self::OPTION_ALL, null, InputOption::VALUE_NONE, 'Describe all Rectors');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $input->getOption(self::OPTION_ALL)) {
            $this->rectorGuard->ensureSomeRectorsAreRegistered();
        }

        $outputFormat = $input->getOption(self::OPTION_FORMAT);

        $this->writeHeadline($input, $outputFormat);

        $this->describeCommandReporter->reportRectorsInFormat($this->getRectorsByInput($input), $outputFormat);

        return 0;
    }

    /**
     * @return RectorInterface[]
     */
    private function getRectorsByInput(InputInterface $input): array
    {
        if (! $input->getOption(self::OPTION_ALL)) {
            return $this->rectorNodeTraverser->getRectors() + $this->yamlFileProcessor->getYamlRectors();
        }

        $robotLoader = $this->createRobotLoaderForAllRectors();
        $robotLoader->rebuild();

        $rectors = [];
        foreach ($robotLoader->getIndexedClasses() as $class => $filename) {
            $reflectionClass = new ReflectionClass($class);
            if ($reflectionClass->isAbstract()) {
                continue;
            }

            /** @var RectorInterface $rector */
            $rectors[] = $reflectionClass->newInstanceWithoutConstructor();
        }

        return $rectors;
    }

    private function createRobotLoaderForAllRectors(): RobotLoader
    {
        $robotLoader = new RobotLoader();

        $robotLoader->addDirectory(__DIR__ . '/../../Rector');
        $robotLoader->addDirectory(__DIR__ . '/../../../packages');
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_rector_finder');
        $robotLoader->acceptFiles = ['*Rector.php'];

        return $robotLoader;
    }

    private function writeHeadline(InputInterface $input, string $outputFormat): void
    {
        if ($outputFormat !== self::FORMAT_MARKDOWN) {
            return;
        }

        $headline = $input->getOption(self::OPTION_ALL) ? '# All Rectors Overview' : '# Rectors Overview';
        $this->consoleStyle->writeln($headline);
        $this->consoleStyle->newLine();
    }
}
