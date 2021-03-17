<?php

declare(strict_types=1);

namespace Rector\Core\Reporting;

use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;

final class MissingRectorRulesReporter
{
    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(RectorNodeTraverser $rectorNodeTraverser, SymfonyStyle $symfonyStyle)
    {
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function reportIfMissing(): ?int
    {
        if ($this->rectorNodeTraverser->getPhpRectorCount() !== 0) {
            return null;
        }

        $this->report();

        return ShellCode::ERROR;
    }

    public function report(): void
    {
        $this->symfonyStyle->warning('We could not find any Rector rules to run. You have 2 options to add them:');

        $this->symfonyStyle->title('1. Add single rule to "rector.php"');
        $this->symfonyStyle->writeln('  $services = $containerConfigurator->services();');
        $this->symfonyStyle->writeln('  $services->set(...);');
        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->title('2. Add set of rules to "rector.php"');
        $this->symfonyStyle->writeln('  $parameters = $containerConfigurator->parameters();');
        $this->symfonyStyle->writeln('  $parameters->set(Option::SETS, [...]);');
        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->title('Missing "rector.php" in your project? Let Rector create it for you');
        $this->symfonyStyle->writeln('  vendor/bin/rector init');
        $this->symfonyStyle->newLine();
    }
}
