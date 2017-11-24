<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Printer\ChangedFilesCollector;
use Rector\Rector\RectorCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProcessCommandReporter
{
    /**
     * @var RectorCollector
     */
    private $rectorCollector;

    /**
     * @var ChangedFilesCollector
     */
    private $changedFilesCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        RectorCollector $rectorCollector,
        ChangedFilesCollector $changedFilesCollector,
        SymfonyStyle $symfonyStyle
    ) {
        $this->rectorCollector = $rectorCollector;
        $this->changedFilesCollector = $changedFilesCollector;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function reportLoadedRectors(): void
    {
        $this->symfonyStyle->title(sprintf(
            '%d Loaded Rector%s',
            $this->rectorCollector->getRectorCount(),
            $this->rectorCollector->getRectorCount() === 1 ? '' : 's'
        ));

        foreach ($this->rectorCollector->getRectors() as $rector) {
            $this->symfonyStyle->writeln(sprintf(
                ' - %s',
                get_class($rector)
            ));
        }

        $this->symfonyStyle->newLine();
    }

    public function reportChangedFiles(): void
    {
        $this->symfonyStyle->title(sprintf(
            '%d Changed File%s',
            $this->changedFilesCollector->getChangedFilesCount(),
            $this->changedFilesCollector->getChangedFilesCount() === 1 ? '' : 's'
        ));

        foreach ($this->changedFilesCollector->getChangedFiles() as $fileInfo) {
            $this->symfonyStyle->writeln(sprintf(
                ' - %s',
                $fileInfo
            ));
        }
    }
}
