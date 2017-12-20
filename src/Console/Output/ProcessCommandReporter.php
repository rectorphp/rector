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
        foreach ($rectorList as $rector) {
            $this->symfonyStyle->writeln(sprintf(
                ' - %s',
                get_class($rector)
            ));
        }

        $this->symfonyStyle->newLine();
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
