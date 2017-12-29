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

        $rectorList = $this->sortByClassName($this->rectorCollector->getRectors());

        $this->consoleStyle->listing(array_map(function (RectorInterface $rector): string {
            return get_class($rector);
        }, $rectorList));
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
     * @param object[] $objects
     * @return object[]
     */
    private function sortByClassName(array $objects): array
    {
        usort($objects, function ($first, $second): int {
            return get_class($first) <=> get_class($second);
        });

        return $objects;
    }
}
