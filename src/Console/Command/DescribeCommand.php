<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Configuration\Option;
use Rector\Console\ConsoleStyle;
use Rector\Console\Output\DescribeCommandReporter;
use Rector\Exception\NoRectorsLoadedException;
use Rector\Naming\CommandNaming;
use Rector\NodeTraverser\RectorNodeTraverser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function __construct(
        ConsoleStyle $consoleStyle,
        RectorNodeTraverser $rectorNodeTraverser,
        DescribeCommandReporter $describeCommandReporter
    ) {
        parent::__construct();

        $this->consoleStyle = $consoleStyle;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->describeCommandReporter = $describeCommandReporter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Shows detailed description of loaded Rectors.');
        $this->addOption(Option::OPTION_NO_DIFFS, null, InputOption::VALUE_NONE, 'Hide examplary diffs.');
        $this->addOption(self::OPTION_FORMAT, null, InputOption::VALUE_REQUIRED, 'Output format.', self::FORMAT_CLI);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureSomeRectorsAreRegistered();

        $outputFormat = $input->getOption(self::OPTION_FORMAT);

        if ($outputFormat === self::FORMAT_MARKDOWN) {
            $this->consoleStyle->writeln('# All Rectors Overview');
            $this->consoleStyle->newLine();
        }

        $this->describeCommandReporter->reportRectorsInFormat(
            $this->rectorNodeTraverser->getRectors(),
            $outputFormat,
            ! $input->getOption(Option::OPTION_NO_DIFFS)
        );

        return 0;
    }

    private function ensureSomeRectorsAreRegistered(): void
    {
        if ($this->rectorNodeTraverser->getRectorCount() > 0) {
            return;
        }

        throw new NoRectorsLoadedException(
            'No rectors were found. Registers them in rector.yml config to "rector:" '
            . 'section, load them via "--config <file>.yml" or "--level <level>" CLI options.'
        );
    }
}
