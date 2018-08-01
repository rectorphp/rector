<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Nette\Utils\Strings;
use Rector\Console\Command\DescribeCommand;
use Rector\Console\ConsoleStyle;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\RectorDefinition\CodeSample;

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
     * @param RectorInterface[] $rectors
     */
    public function reportRectorsInFormat(array $rectors, string $outputFormat, bool $showDiffs): void
    {
        if ($outputFormat === DescribeCommand::FORMAT_CLI) {
            $i = 0;
            foreach ($rectors as $rector) {
                $this->printWithCliFormat(++$i, $showDiffs, $rector);
            }
            return;
        }

        $rectorsByGroup = $this->groupRectors($rectors);
        $this->printMenu($rectorsByGroup);

        foreach ($rectorsByGroup as $group => $rectors) {
            $this->consoleStyle->writeln('## ' . $group);
            $this->consoleStyle->newLine();

            foreach ($rectors as $rector) {
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

        $formattedDiff = $this->markdownDifferAndFormatter->bareDiffAndFormat($codeBefore, $codeAfter);
        if ($formattedDiff) {
            $this->consoleStyle->write($formattedDiff);
        }
    }

    private function printWithMarkdownFormat(bool $showDiffs, RectorInterface $rector): void
    {
        $rectorClass = get_class($rector);
        $rectorClassParts = explode('\\', $rectorClass);
        $headline = $rectorClassParts[count($rectorClassParts) - 1];

        $this->consoleStyle->writeln(sprintf('### `%s`', $headline));

        $this->consoleStyle->newLine();
        $this->consoleStyle->writeln(sprintf('- class: `%s`', $rectorClass));

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
        if (Strings::match($rectorClass, '#^Rector\(Yaml)?Rector#')) {
            return $rectorClassParts[count($rectorClassParts) - 2];
        }

        // Rector/<PackageGroup>/Rector/SomeRector
        if (count($rectorClassParts) === 4) {
            return $rectorClassParts[1];
        }

        // Rector/<PackageGroup>/Rector/<PackageSubGroup>/SomeRector
        if (count($rectorClassParts) === 5) {
            return $rectorClassParts[1] . '\\' . $rectorClassParts[3];
        }

        // fallback
        return $rectorClassParts[count($rectorClassParts) - 2];
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
}
