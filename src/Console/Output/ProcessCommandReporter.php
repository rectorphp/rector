<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Console\ConsoleStyle;
use Rector\Contract\Rector\RectorInterface;
use Rector\Rector\RectorCollector;

final class ProcessCommandReporter
{
    /**
     * @var RectorCollector
     */
    private $rectorCollector;

    /**
     * @var ConsoleStyle
     */
    private $consoleStyle;

    public function __construct(RectorCollector $rectorCollector, ConsoleStyle $consoleStyle)
    {
        $this->rectorCollector = $rectorCollector;
        $this->consoleStyle = $consoleStyle;
    }

    public function reportLoadedRectors(): void
    {
        $this->consoleStyle->title(sprintf(
            '%d Loaded Rector%s',
            $this->rectorCollector->getRectorCount(),
            $this->rectorCollector->getRectorCount() === 1 ? '' : 's'
        ));

        $rectorClasses = array_map(function (RectorInterface $rector): string {
            return get_class($rector);
        }, $this->rectorCollector->getRectors());

        $this->consoleStyle->listing($rectorClasses);
    }

    /**
     * @param string[] $changedFiles
     */
    public function reportChangedFiles(array $changedFiles): void
    {
        $this->consoleStyle->title(sprintf(
            '%d Changed file%s',
            count($changedFiles),
            count($changedFiles) === 1 ? '' : 's'
        ));
        $this->consoleStyle->listing($changedFiles);
    }

    /**
     * @param string[][] $diffFiles
     */
    public function reportDiffFiles(array $diffFiles): void
    {
        $this->consoleStyle->title(sprintf(
            '%d file%s with changes',
            count($diffFiles),
            count($diffFiles) === 1 ? '' : 's'
        ));

        foreach ($diffFiles as $diffFile) {
            $this->consoleStyle->writeln($diffFile['file']);
            $this->consoleStyle->newLine();
            $this->consoleStyle->writeln($diffFile['diff']);
            $this->consoleStyle->newLine();
        }
    }
}
