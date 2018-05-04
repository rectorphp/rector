<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Console\Command\DescribeCommand;
use Rector\Console\ConsoleStyle;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\RectorDefinition\CodeSample;

final class DescribeCommandReporter
{
    /**
     * @var ConsoleStyle
     */
    private $consoleStyle;

    /**
     * @var DifferAndFormatter
     */
    private $differAndFormatter;

    public function __construct(ConsoleStyle $consoleStyle, DifferAndFormatter $differAndFormatter)
    {
        $this->consoleStyle = $consoleStyle;
        $this->differAndFormatter = $differAndFormatter;
    }

    /**
     * @param RectorInterface[] $rectors
     */
    public function reportRectorsInFormat(array $rectors, string $outputFormat, bool $showDiffs): void
    {
        $i = 0;
        foreach ($rectors as $rector) {
            if ($outputFormat === DescribeCommand::FORMAT_CLI) {
                $this->printWithCliFormat(++$i, $showDiffs, $rector);
            } elseif ($outputFormat === DescribeCommand::FORMAT_MARKDOWN) {
                $this->printWithMarkdownFormat($showDiffs, $rector);
            }
        }
    }

    private function printWithCliFormat(int $i, bool $showDiffs, RectorInterface $rector): void
    {
        $this->consoleStyle->section(sprintf('%d) %s', $i, get_class($rector)));

        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription()) {
            $this->consoleStyle->writeln(' * ' . $rectorDefinition->getDescription());
        }

        if ($showDiffs) {
            $this->describeRectorCodeSamples($rectorDefinition->getCodeSamples());
        }

        $this->consoleStyle->newLine(2);
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

    private function printWithMarkdownFormat(bool $showDiffs, RectorInterface $rector): void
    {
        $this->consoleStyle->writeln('## ' . get_class($rector));

        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription()) {
            $this->consoleStyle->newLine();
            $this->consoleStyle->writeln($rectorDefinition->getDescription());
        }

        if ($showDiffs) {
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
}
