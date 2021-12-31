<?php

declare (strict_types=1);
namespace Rector\Core\Reporting;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix20211231\Symfony\Component\Console\Command\Command;
use RectorPrefix20211231\Symfony\Component\Console\Style\SymfonyStyle;
final class MissingRectorRulesReporter
{
    /**
     * @var RectorInterface[]
     * @readonly
     */
    private $rectors;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors, \RectorPrefix20211231\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->rectors = $rectors;
        $this->symfonyStyle = $symfonyStyle;
    }
    public function reportIfMissing() : ?int
    {
        $activeRectors = \array_filter($this->rectors, function (\Rector\Core\Contract\Rector\RectorInterface $rector) : bool {
            if ($rector instanceof \Rector\PostRector\Contract\Rector\PostRectorInterface) {
                return \false;
            }
            return !$rector instanceof \Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
        });
        if ($activeRectors !== []) {
            return null;
        }
        $this->report();
        return \RectorPrefix20211231\Symfony\Component\Console\Command\Command::FAILURE;
    }
    public function report() : void
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
