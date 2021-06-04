<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Output;

use Nette\Utils\Strings;
use Rector\ChangesReporting\Annotation\RectorsChangelogResolver;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ValueObject\Application\RectorError;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObject\Reporting\FileDiff;

final class ConsoleOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'console';

    /**
     * @var string
     * @see https://regex101.com/r/q8I66g/1
     */
    private const ON_LINE_REGEX = '# on line #';

    public function __construct(
        private Configuration $configuration,
        private OutputStyleInterface $outputStyle,
        private RectorsChangelogResolver $rectorsChangelogResolver
    ) {
    }

    public function report(ProcessResult $processResult): void
    {
        if ($this->configuration->getOutputFile()) {
            $message = sprintf(
                'Option "--%s" can be used only with "--%s %s"',
                Option::OPTION_OUTPUT_FILE,
                Option::OPTION_OUTPUT_FORMAT,
                'json'
            );
            $this->outputStyle->error($message);
        }

        if ($this->configuration->shouldShowDiffs()) {
            $this->reportFileDiffs($processResult->getFileDiffs());
        }

        $this->reportErrors($processResult->getErrors());
        $this->reportRemovedFilesAndNodes($processResult);

        if ($processResult->getErrors() !== []) {
            return;
        }

        $message = $this->createSuccessMessage($processResult);
        $this->outputStyle->success($message);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param FileDiff[] $fileDiffs
     */
    private function reportFileDiffs(array $fileDiffs): void
    {
        if (count($fileDiffs) <= 0) {
            return;
        }

        // normalize
        ksort($fileDiffs);
        $message = sprintf('%d file%s with changes', count($fileDiffs), count($fileDiffs) === 1 ? '' : 's');

        $this->outputStyle->title($message);

        $i = 0;
        foreach ($fileDiffs as $fileDiff) {
            $relativeFilePath = $fileDiff->getRelativeFilePath();
            $message = sprintf('<options=bold>%d) %s</>', ++$i, $relativeFilePath);

            $this->outputStyle->writeln($message);
            $this->outputStyle->newLine();
            $this->outputStyle->writeln($fileDiff->getDiffConsoleFormatted());

            $rectorsChangelogsLines = $this->createRectorChangelogLines($fileDiff);

            if ($fileDiff->getRectorChanges() !== []) {
                $this->outputStyle->writeln('<options=underscore>Applied rules:</>');
                $this->outputStyle->listing($rectorsChangelogsLines);
                $this->outputStyle->newLine();
            }
        }
    }

    /**
     * @param RectorError[] $errors
     */
    private function reportErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $errorMessage = $error->getMessage();
            $errorMessage = $this->normalizePathsToRelativeWithLine($errorMessage);

            $message = sprintf(
                'Could not process "%s" file%s, due to: %s"%s".',
                $error->getRelativeFilePath(),
                $error->getRectorClass() ? ' by "' . $error->getRectorClass() . '"' : '',
                PHP_EOL,
                $errorMessage
            );

            if ($error->getLine()) {
                $message .= ' On line: ' . $error->getLine();
            }

            $this->outputStyle->error($message);
        }
    }

    private function reportRemovedFilesAndNodes(ProcessResult $processResult): void
    {
        if ($processResult->getAddedFilesCount() !== 0) {
            $message = sprintf('%d files were added', $processResult->getAddedFilesCount());
            $this->outputStyle->note($message);
        }

        if ($processResult->getRemovedFilesCount() !== 0) {
            $message = sprintf('%d files were removed', $processResult->getRemovedFilesCount());
            $this->outputStyle->note($message);
        }

        $this->reportRemovedNodes($processResult);
    }

    private function normalizePathsToRelativeWithLine(string $errorMessage): string
    {
        $regex = '#' . preg_quote(getcwd(), '#') . '/#';
        $errorMessage = Strings::replace($errorMessage, $regex, '');
        return Strings::replace($errorMessage, self::ON_LINE_REGEX, ':');
    }

    private function reportRemovedNodes(ProcessResult $processResult): void
    {
        if ($processResult->getRemovedNodeCount() === 0) {
            return;
        }

        $message = sprintf('%d nodes were removed', $processResult->getRemovedNodeCount());
        $this->outputStyle->warning($message);
    }

    private function createSuccessMessage(ProcessResult $processResult): string
    {
        $changeCount = count($processResult->getFileDiffs()) + $processResult->getRemovedAndAddedFilesCount();

        if ($changeCount === 0) {
            return 'Rector is done!';
        }

        return sprintf(
            '%d file%s %s by Rector',
            $changeCount,
            $changeCount > 1 ? 's' : '',
            $this->configuration->isDryRun() ? 'would have changed (dry-run)' : ($changeCount === 1 ? 'has' : 'have') . ' been changed'
        );
    }

    /**
     * @return string[]
     */
    private function createRectorChangelogLines(FileDiff $fileDiff): array
    {
        $rectorsChangelogs = $this->rectorsChangelogResolver->resolveIncludingMissing($fileDiff->getRectorClasses());

        $rectorsChangelogsLines = [];
        foreach ($rectorsChangelogs as $rectorClass => $changelog) {
            $rectorShortClass = (string) Strings::after($rectorClass, '\\', -1);
            $rectorsChangelogsLines[] = $changelog === null ? $rectorShortClass : $rectorShortClass . ' (' . $changelog . ')';
        }

        return $rectorsChangelogsLines;
    }
}
