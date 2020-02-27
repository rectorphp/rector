<?php

declare(strict_types=1);

namespace Rector\Compiler\Differ;

use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleDiffer
{
    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ConsoleDifferFormatter
     */
    private $consoleDifferFormatter;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        Differ $differ,
        ConsoleDifferFormatter $consoleDifferFormatter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->differ = $differ;
        $this->consoleDifferFormatter = $consoleDifferFormatter;
    }

    public function diff(string $old, string $new): void
    {
        $diff = $this->differ->diff($old, $new);
        $consoleFormatted = $this->consoleDifferFormatter->format($diff);
        $this->symfonyStyle->writeln($consoleFormatted);
    }
}
