<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use RectorPrefix202407\Nette\Utils\Strings;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\ProcessResult;
use Rector\ValueObject\Reporting\FileDiff;
use RectorPrefix202407\Symfony\Component\Console\Style\SymfonyStyle;
final class ConsoleOutputFormatter implements OutputFormatterInterface
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var string
     */
    public const NAME = 'console';
    /**
     * @var string
     * @see https://regex101.com/r/q8I66g/1
     */
    private const ON_LINE_REGEX = '# on line #';
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function report(ProcessResult $processResult, Configuration $configuration) : void
    {
        if ($configuration->shouldShowDiffs()) {
            $this->reportFileDiffs($processResult->getFileDiffs());
        }
        $this->reportErrors($processResult->getSystemErrors());
        if ($processResult->getSystemErrors() !== []) {
            return;
        }
        // to keep space between progress bar and success message
        if ($configuration->shouldShowProgressBar() && $processResult->getFileDiffs() === []) {
            $this->symfonyStyle->newLine();
        }
        $message = $this->createSuccessMessage($processResult, $configuration);
        $this->symfonyStyle->success($message);
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
        $this->symfonyStyle->title($message);
        $i = 0;
        foreach ($fileDiffs as $fileDiff) {
            $relativeFilePath = $fileDiff->getRelativeFilePath();
            // append line number for faster file jump in diff
            $firstLineNumber = $fileDiff->getFirstLineNumber();
            if ($firstLineNumber !== null) {
                $relativeFilePath .= ':' . $firstLineNumber;
            }
            $message = \sprintf('<options=bold>%d) %s</>', ++$i, $relativeFilePath);
            $this->symfonyStyle->writeln($message);
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln($fileDiff->getDiffConsoleFormatted());
            if ($fileDiff->getRectorChanges() !== []) {
                $this->symfonyStyle->writeln('<options=underscore>Applied rules:</>');
                $this->symfonyStyle->listing($fileDiff->getRectorShortClasses());
                $this->symfonyStyle->newLine();
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
            $this->symfonyStyle->error($message);
        }
    }
    private function normalizePathsToRelativeWithLine(string $errorMessage) : string
    {
        $regex = '#' . \preg_quote(\getcwd(), '#') . '/#';
        $errorMessage = Strings::replace($errorMessage, $regex);
        return Strings::replace($errorMessage, self::ON_LINE_REGEX);
    }
    private function createSuccessMessage(ProcessResult $processResult, Configuration $configuration) : string
    {
        $changeCount = \count($processResult->getFileDiffs());
        if ($changeCount === 0) {
            return 'Rector is done!';
        }
        return \sprintf('%d file%s %s by Rector', $changeCount, $changeCount > 1 ? 's' : '', $configuration->isDryRun() ? 'would have been changed (dry-run)' : ($changeCount === 1 ? 'has' : 'have') . ' been changed');
    }
}
