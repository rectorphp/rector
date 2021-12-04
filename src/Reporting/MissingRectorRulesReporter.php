<?php

declare(strict_types=1);

namespace Rector\Core\Reporting;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MissingRectorRulesReporter
{
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(
        private readonly array $rectors,
        private readonly SymfonyStyle $symfonyStyle
    ) {
    }

    public function reportIfMissing(): ?int
    {
        $activeRectors = array_filter(
            $this->rectors,
            function (RectorInterface $rector): bool {
                if ($rector instanceof PostRectorInterface) {
                    return false;
                }

                return ! $rector instanceof ComplementaryRectorInterface;
            }
        );

        if ($activeRectors !== []) {
            return null;
        }

        $this->report();

        return Command::FAILURE;
    }

    public function report(): void
    {
        $this->symfonyStyle->warning('We could not find any Rector rules to run. You have 2 options to add them:');

        $this->symfonyStyle->title('1. Add single rule to "rector.php"');
        $this->symfonyStyle->writeln('  $services = $containerConfigurator->services();');
        $this->symfonyStyle->writeln('  $services->set(...);');
        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->title('2. Add set of rules to "rector.php"');
        $this->symfonyStyle->writeln('  $containerConfigurator->import(SetList::...);');
        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->title('Missing "rector.php" in your project? Let Rector create it for you');
        $this->symfonyStyle->writeln('  vendor/bin/rector init');
        $this->symfonyStyle->newLine();
    }
}
