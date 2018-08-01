<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Loaders\RobotLoader;
use Rector\Configuration\Option;
use Rector\Console\ConsoleStyle;
use Rector\Console\Output\DescribeCommandReporter;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\YamlRector\YamlFileProcessor;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class DescribeCommand extends Command
{
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
     * @var YamlFileProcessor
     */
    private $yamlFileProcessor;

    public function __construct(
        ConsoleStyle $consoleStyle,
        RectorNodeTraverser $rectorNodeTraverser,
        DescribeCommandReporter $describeCommandReporter,
        YamlFileProcessor $yamlFileProcessor
    ) {
        parent::__construct();

        $this->consoleStyle = $consoleStyle;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->describeCommandReporter = $describeCommandReporter;
        $this->yamlFileProcessor = $yamlFileProcessor;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Shows detailed description of loaded Rectors.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->writeHeadline($input);
        $this->describeCommandReporter->reportRectorsInFormat($this->getRectorsByInput($input));

        return 0;
    }

    /**
     * @return RectorInterface[]
     */
    private function getRectorsByInput(InputInterface $input): array
    {
        if ($input->getOption(Option::OPTION_LEVEL)) {
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

    private function writeHeadline(InputInterface $input): void
    {
        $headline = $input->getOption(Option::OPTION_LEVEL) ? sprintf(
            '# Rectors for %s level',
            $input->getOption(Option::OPTION_LEVEL)
        ) : '# All Rectors Overview';
        $this->consoleStyle->writeln($headline);
        $this->consoleStyle->newLine();
    }
}
