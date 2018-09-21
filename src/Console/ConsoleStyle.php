<?php declare(strict_types=1);

namespace Rector\Console;

use Symfony\Component\Console\Style\SymfonyStyle;
use function Safe\sprintf;

final class ConsoleStyle extends SymfonyStyle
{
    /**
     * @param string[] $elements
     */
    public function listing(array $elements): void
    {
        $elements = array_map(function ($element) {
            return sprintf(' - %s', $element);
        }, $elements);
        $this->writeln($elements);
        $this->newLine();
    }
}
