<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Rector\RectorCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProcessCommandReporter
{
    /**
     * @var RectorCollector
     */
    private $rectorCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(RectorCollector $rectorCollector, SymfonyStyle $symfonyStyle)
    {
        $this->rectorCollector = $rectorCollector;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function reportLoadedRectors(): void
    {
        $this->symfonyStyle->title(sprintf(
            '%d Loaded Rector%s',
            $this->rectorCollector->getRectorCount(),
            $this->rectorCollector->getRectorCount() === 1 ? '' : 's'
        ));

        $rectorList = $this->sortByClassName($this->rectorCollector->getRectors());

        $this->symfonyStyle->listing(array_map(function ($rector): string {
            return get_class($rector);
        }, $rectorList));
    }

    /**
     * @param string[] $changedFiles
     */
    public function reportChangedFiles(array $changedFiles): void
    {
        $this->symfonyStyle->title(sprintf(
            '%s Changed File%s',
            count($changedFiles),
            count($changedFiles) === 1 ? '' : 's'
        ));
        $this->symfonyStyle->listing($changedFiles);
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
