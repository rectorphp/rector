<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Configuration\Option;
use Rector\Console\ConsoleStyle;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\Naming\CommandNaming;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\RectorDefinition\CodeSample;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var DifferAndFormatter
     */
    private $differAndFormatter;

    public function __construct(
        ConsoleStyle $consoleStyle,
        RectorNodeTraverser $rectorNodeTraverser,
        DifferAndFormatter $differAndFormatter
    ) {
        parent::__construct();

        $this->consoleStyle = $consoleStyle;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->differAndFormatter = $differAndFormatter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Shows detailed description of loaded Rectors.');
        $this->addOption(
            Option::DESCRIBE_WITH_DIFFS,
            null,
            InputOption::VALUE_OPTIONAL,
            'See exemplary diffs.',
            true
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->rectorNodeTraverser->getRectors() as $rector) {
            $this->describeRector($input, $rector);
        }

        $this->consoleStyle->success('Rector is done!');

        return 0;
    }

    private function describeRector(InputInterface $input, RectorInterface $rector): void
    {
        $this->consoleStyle->section(get_class($rector));

        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription()) {
            $this->consoleStyle->writeln(' * ' . $rectorDefinition->getDescription());
        }

        if ($input->getOption(Option::DESCRIBE_WITH_DIFFS)) {
            $this->describeRectorCodeSamples($rectorDefinition->getCodeSamples());
        }

        $this->consoleStyle->newLine();
    }

    /**
     * @param CodeSample[] $codeSamples
     */
    private function describeRectorCodeSamples(array $codeSamples): void
    {
        foreach ($codeSamples as $codeSample) {
            $this->consoleStyle->newLine();

            $formattedDiff = $this->differAndFormatter->bareDiffAndFormat(
                $codeSample->getCodeBefore(),
                $codeSample->getCodeAfter()
            );

            if ($formattedDiff) {
                $this->consoleStyle->write($formattedDiff);
            }
        }
    }
}
