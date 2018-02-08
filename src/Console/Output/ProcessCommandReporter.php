<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Console\ConsoleStyle;
use Rector\Contract\Rector\RectorInterface;
use Rector\Rector\RectorCollector;
use Rector\Reporting\FileDiff;

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
        if (count($changedFiles) <= 0) {
            return;
        }

        $this->consoleStyle->title(sprintf(
            '%d Changed file%s',
            count($changedFiles),
            count($changedFiles) === 1 ? '' : 's'
        ));
        $this->consoleStyle->listing($changedFiles);
    }

    /**
     * @param FileDiff[] $fileDiffs
     */
    public function reportFileDiffs(array $fileDiffs): void
    {
        if (count($fileDiffs) <= 0) {
            return;
        }

        $this->consoleStyle->title(sprintf(
            '%d file%s with changes',
            count($fileDiffs),
            count($fileDiffs) === 1 ? '' : 's'
        ));

        $i = 0;
        foreach ($fileDiffs as $fileDiff) {
            $this->consoleStyle->writeln(sprintf('<options=bold>%d) %s</>', ++$i, $fileDiff->getFile()));
            $this->consoleStyle->newLine();
            $this->consoleStyle->writeln($fileDiff->getDiff());
            $this->consoleStyle->newLine();
        }
    }
}
