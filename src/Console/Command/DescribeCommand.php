<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Configuration\Option;
use Rector\Console\ConsoleStyle;
use Rector\Console\Output\DescribeCommandReporter;
use Rector\Guard\RectorGuard;
use Rector\NodeTraverser\RectorNodeTraverser;
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

    public function __construct(
        ConsoleStyle $consoleStyle,
        RectorNodeTraverser $rectorNodeTraverser,
        DescribeCommandReporter $describeCommandReporter,
        RectorGuard $rectorGuard
    ) {
        parent::__construct();

        $this->consoleStyle = $consoleStyle;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->describeCommandReporter = $describeCommandReporter;
        $this->rectorGuard = $rectorGuard;
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
        $this->rectorGuard->ensureSomeRectorsAreRegistered();

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
}
