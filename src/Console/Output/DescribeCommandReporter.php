<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Console\Command\DescribeCommand;
use Rector\Console\ConsoleStyle;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\RectorDefinition\CodeSample;
use Rector\YamlRector\Contract\YamlRectorInterface;

final class DescribeCommandReporter
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
        $this->consoleStyle = $consoleStyle;
        $this->markdownDifferAndFormatter = $markdownDifferAndFormatter;
    }

    /**
     * @param RectorInterface[]|YamlRectorInterface[] $rectors
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

    /**
     * @param RectorInterface|YamlRectorInterface $rector
     */
    private function printWithCliFormat(int $i, bool $showDiffs, $rector): void
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

        $formattedDiff = $this->markdownDifferAndFormatter->bareDiffAndFormat($codeBefore, $codeAfter);
        if ($formattedDiff) {
            $this->consoleStyle->write($formattedDiff);
        }
    }

    /**
     * @param RectorInterface|YamlRectorInterface $rector
     */
    private function printWithMarkdownFormat(bool $showDiffs, $rector): void
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

            $diff = $this->markdownDifferAndFormatter->bareDiffAndFormatWithoutColors($codeBefore, $codeAfter);

            $this->consoleStyle->write($diff);
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
}
