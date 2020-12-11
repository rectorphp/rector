<?php

declare(strict_types=1);

namespace Rector\Core\Bootstrap;

use Symfony\Component\Console\Style\SymfonyStyle;

final class NoRectorsLoadedReporter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function report(): void
    {
        $this->symfonyStyle->error('We could not find any Rector rules to run');

        $this->symfonyStyle->writeln('You have few options to add them:');
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->title('Add single rule to "rector.php"');
        $this->symfonyStyle->writeln('  $services = $containerConfigurator->services();');
        $this->symfonyStyle->writeln('  $services->set(...);');
        $this->symfonyStyle->newLine(2);

        $this->symfonyStyle->title('Add set of rules to "rector.php"');
        $this->symfonyStyle->writeln('  $parameters = $containerConfigurator->parameters();');
        $this->symfonyStyle->writeln('  $parameters->set(Option::SETS, [...]);');
        $this->symfonyStyle->newLine(2);

        $this->symfonyStyle->title('Missing "rector.php" in your project? Let Rector create it for you');
        $this->symfonyStyle->writeln('  vendor/bin/rector init');
        $this->symfonyStyle->newLine();
    }
}
