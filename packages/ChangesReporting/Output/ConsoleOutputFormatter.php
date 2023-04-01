<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use RectorPrefix202304\Nette\Utils\Strings;
use Rector\ChangesReporting\Annotation\RectorsChangelogResolver;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
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
    /**
     * @readonly
     * @var \Rector\Core\Contract\Console\OutputStyleInterface
     */
    private $rectorOutputStyle;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Annotation\RectorsChangelogResolver
     */
    private $rectorsChangelogResolver;
    public function __construct(OutputStyleInterface $rectorOutputStyle, RectorsChangelogResolver $rectorsChangelogResolver)
    {
        $this->rectorOutputStyle = $rectorOutputStyle;
        $this->rectorsChangelogResolver = $rectorsChangelogResolver;
    }
    public function report(ProcessResult $processResult, Configuration $configuration) : void
    {
        if ($configuration->shouldShowDiffs()) {
            $this->reportFileDiffs($processResult->getFileDiffs());
        }
        $this->reportErrors($processResult->getErrors());
        $this->reportRemovedFilesAndNodes($processResult);
        if ($processResult->getErrors() !== []) {
            return;
        }
        // to keep space between progress bar and success message
        if ($configuration->shouldShowProgressBar() && $processResult->getFileDiffs() === []) {
            $this->rectorOutputStyle->newLine();
        }
        $message = $this->createSuccessMessage($processResult, $configuration);
        $this->rectorOutputStyle->success($message);
    }
    public function getName() : string
    {
        return self::NAME;
    }
    /**
     * @param FileDiff[] $fileDiffs
     */
    private function reportFileDiffs(array $fileDiffs) : void
    {
        if (\count($fileDiffs) <= 0) {
            return;
        }
        // normalize
        \ksort($fileDiffs);
        $message = \sprintf('%d file%s with changes', \count($fileDiffs), \count($fileDiffs) === 1 ? '' : 's');
        $this->rectorOutputStyle->title($message);
        $i = 0;
        foreach ($fileDiffs as $fileDiff) {
            $relativeFilePath = $fileDiff->getRelativeFilePath();
            // append line number for faster file jump in diff
            $firstLineNumber = $fileDiff->getFirstLineNumber();
            if ($firstLineNumber !== null) {
                $relativeFilePath .= ':' . $firstLineNumber;
            }
            $message = \sprintf('<options=bold>%d) %s</>', ++$i, $relativeFilePath);
            $this->rectorOutputStyle->writeln($message);
            $this->rectorOutputStyle->newLine();
            $this->rectorOutputStyle->writeln($fileDiff->getDiffConsoleFormatted());
            $rectorsChangelogsLines = $this->createRectorChangelogLines($fileDiff);
            if ($fileDiff->getRectorChanges() !== []) {
                $this->rectorOutputStyle->writeln('<options=underscore>Applied rules:</>');
                $this->rectorOutputStyle->listing($rectorsChangelogsLines);
                $this->rectorOutputStyle->newLine();
            }
        }
    }
    /**
     * @param SystemError[] $errors
     */
    private function reportErrors(array $errors) : void
    {
        foreach ($errors as $error) {
            $errorMessage = $error->getMessage();
            $errorMessage = $this->normalizePathsToRelativeWithLine($errorMessage);
            $message = \sprintf('Could not process %s%s, due to: %s"%s".', $error->getFile() !== null ? '"' . $error->getFile() . '" file' : 'some files', $error->getRectorClass() !== null ? ' by "' . $error->getRectorClass() . '"' : '', \PHP_EOL, $errorMessage);
            if ($error->getLine() !== null) {
                $message .= ' On line: ' . $error->getLine();
            }
            $this->rectorOutputStyle->error($message);
        }
    }
    private function reportRemovedFilesAndNodes(ProcessResult $processResult) : void
    {
        if ($processResult->getAddedFilesCount() !== 0) {
            $message = \sprintf('%d files were added', $processResult->getAddedFilesCount());
            $this->rectorOutputStyle->note($message);
        }
        if ($processResult->getRemovedFilesCount() !== 0) {
            $message = \sprintf('%d files were removed', $processResult->getRemovedFilesCount());
            $this->rectorOutputStyle->note($message);
        }
        $this->reportRemovedNodes($processResult);
    }
    private function normalizePathsToRelativeWithLine(string $errorMessage) : string
    {
        $regex = '#' . \preg_quote(\getcwd(), '#') . '/#';
        $errorMessage = Strings::replace($errorMessage, $regex);
        return Strings::replace($errorMessage, self::ON_LINE_REGEX);
    }
    private function reportRemovedNodes(ProcessResult $processResult) : void
    {
        if ($processResult->getRemovedNodeCount() === 0) {
            return;
        }
        $message = \sprintf('%d nodes were removed', $processResult->getRemovedNodeCount());
        $this->rectorOutputStyle->warning($message);
    }
    private function createSuccessMessage(ProcessResult $processResult, Configuration $configuration) : string
    {
        $changeCount = \count($processResult->getFileDiffs()) + $processResult->getRemovedAndAddedFilesCount();
        if ($changeCount === 0) {
            return 'Rector is done!';
        }
        return \sprintf('%d file%s %s by Rector', $changeCount, $changeCount > 1 ? 's' : '', $configuration->isDryRun() ? 'would have changed (dry-run)' : ($changeCount === 1 ? 'has' : 'have') . ' been changed');
    }
    /**
     * @return string[]
     */
    private function createRectorChangelogLines(FileDiff $fileDiff) : array
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
