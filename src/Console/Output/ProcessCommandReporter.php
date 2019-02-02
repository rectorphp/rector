<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Application\Error;
use Rector\Application\RemovedFilesCollector;
use Rector\Configuration\Configuration;
use Rector\Reporting\FileDiff;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProcessCommandReporter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var RemovedFilesCollector
     */
    private $removedFilesCollector;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        RemovedFilesCollector $removedFilesCollector,
        Configuration $configuration
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->removedFilesCollector = $removedFilesCollector;
        $this->configuration = $configuration;
    }

    /**
     * @param FileDiff[] $fileDiffs
     */
    public function reportFileDiffs(array $fileDiffs): void
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
            $this->symfonyStyle->writeln(sprintf('<options=bold>%d) %s</>', ++$i, $fileDiff->getFile()));
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln($fileDiff->getDiff());
            $this->symfonyStyle->newLine();

            if ($fileDiff->getAppliedRectorClasses()) {
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
    public function reportErrors(array $errors): void
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

    public function reportRemovedFiles(): void
    {
        foreach ($this->removedFilesCollector->getFiles() as $smartFileInfo) {
            if ($this->configuration->isDryRun()) {
                $this->symfonyStyle->warning(sprintf('File "%s" will be removed', $smartFileInfo->getRealPath()));
            } else {
                // removed file has no real path anymore
                $this->symfonyStyle->warning(sprintf('File "%s" was be removed', $smartFileInfo->getPathname()));
            }
        }
    }
}
