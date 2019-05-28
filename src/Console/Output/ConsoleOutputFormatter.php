<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Application\Error;
use Rector\Application\ErrorAndDiffCollector;
use Rector\Contract\Console\Output\OutputFormatterInterface;
use Rector\Reporting\FileDiff;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'console';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function report(ErrorAndDiffCollector $errorAndDiffCollector): void
    {
        $this->reportFileDiffs($errorAndDiffCollector->getFileDiffs());
        $this->reportErrors($errorAndDiffCollector->getErrors());

        if ($errorAndDiffCollector->getErrors() !== []) {
            return;
        }

        $this->symfonyStyle->success(sprintf(
            'Rector is done! %d changed files',
            count($errorAndDiffCollector->getFileDiffs()) + $errorAndDiffCollector->getRemovedAndAddedFilesCount()
        ));
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

        $this->symfonyStyle->title(
            sprintf('%d file%s with changes', count($fileDiffs), count($fileDiffs) === 1 ? '' : 's')
        );

        $i = 0;
        foreach ($fileDiffs as $fileDiff) {
            $relativeFilePath = $fileDiff->getSmartFileInfo()->getRelativeFilePath();

            $this->symfonyStyle->writeln(sprintf('<options=bold>%d) %s</>', ++$i, $relativeFilePath));
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln($fileDiff->getDiffConsoleFormatted());
            $this->symfonyStyle->newLine();

            if ($fileDiff->getAppliedRectorClasses() !== []) {
                $this->symfonyStyle->writeln('Applied rectors:');
                $this->symfonyStyle->newLine();
                $this->symfonyStyle->listing($fileDiff->getAppliedRectorClasses());
                $this->symfonyStyle->newLine();
            }
        }
    }

    /**
     * @param Error[] $errors
     */
    private function reportErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $message = sprintf(
                'Could not process "%s" file%s, due to: %s"%s".',
                $error->getFileInfo()->getPathname(),
                $error->getRectorClass() ? ' by "' . $error->getRectorClass() . '"' : '',
                PHP_EOL,
                $error->getMessage()
            );

            if ($error->getLine()) {
                $message .= ' On line: ' . $error->getLine();
            }

            $this->symfonyStyle->error($message);
        }
    }
}
