<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Configuration\Option;
use Rector\Console\ConsoleStyle;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\NoRectorsLoadedException;
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
     * @var string
     */
    private const FORMAT_CLI = 'cli';

    /**
     * @var string
     */
    private const FORMAT_MARKDOWN = 'md';

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

        $i = 0;
        foreach ($this->rectorNodeTraverser->getRectors() as $rector) {
            if ($outputFormat === self::FORMAT_CLI) {
                $this->printWithCliFormat(++$i, $input, $rector);
            } elseif ($outputFormat === self::FORMAT_MARKDOWN) {
                $this->printWithMarkdownFormat($input, $rector);
            }
        }

        return 0;
    }

    /**
     * @param CodeSample[] $codeSamples
     */
    private function describeRectorCodeSamples(array $codeSamples): void
    {
        [$codeBefore, $codeAfter] = $this->joinBeforeAndAfter($codeSamples);

        $formattedDiff = $this->differAndFormatter->bareDiffAndFormat($codeBefore, $codeAfter);
        if ($formattedDiff) {
            $this->consoleStyle->write($formattedDiff);
        }
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

    /**
     * @param CodeSample[] $codeSamples
     * @return string[]
     */
    private function joinBeforeAndAfter(array $codeSamples): array
    {
        $codeBefore = '';
        $codeAfter = '';
        $separator = PHP_EOL . PHP_EOL;

        foreach ($codeSamples as $codeSample) {
            $codeBefore .= $codeSample->getCodeBefore() . $separator;
            $codeAfter .= $codeSample->getCodeAfter() . $separator;
        }

        return [$codeBefore, $codeAfter];
    }

    private function printWithCliFormat(int $i, InputInterface $input, RectorInterface $rector): void
    {
        $this->consoleStyle->section(sprintf('%d) %s', $i, get_class($rector)));

        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription()) {
            $this->consoleStyle->writeln(' * ' . $rectorDefinition->getDescription());
        }

        if (! $input->getOption(Option::OPTION_NO_DIFFS)) {
            $this->describeRectorCodeSamples($rectorDefinition->getCodeSamples());
        }

        $this->consoleStyle->newLine(2);
    }

    private function printWithMarkdownFormat(InputInterface $input, RectorInterface $rector): void
    {
        $this->consoleStyle->writeln('## ' . get_class($rector));

        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription()) {
            $this->consoleStyle->newLine();
            $this->consoleStyle->writeln($rectorDefinition->getDescription());
        }

        if (! $input->getOption(Option::OPTION_NO_DIFFS)) {
            $this->consoleStyle->newLine();
            $this->consoleStyle->writeln('```diff');

            [$codeBefore, $codeAfter] = $this->joinBeforeAndAfter($rectorDefinition->getCodeSamples());

            $diff = $this->differAndFormatter->bareDiffAndFormatWithoutColors($codeBefore, $codeAfter);

            $this->consoleStyle->write(trim($diff));
            $this->consoleStyle->newLine();
            $this->consoleStyle->writeln('```');
        }

        $this->consoleStyle->newLine(1);
    }
}
