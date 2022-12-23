<?php

declare (strict_types=1);
namespace Rector\Core\Reporting;

use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix202212\Symfony\Component\Console\Command\Command;
final class MissingRectorRulesReporter
{
    /**
     * @var RectorInterface[]
     * @readonly
     */
    private $rectors;
    /**
     * @readonly
     * @var \Rector\Core\Contract\Console\OutputStyleInterface
     */
    private $rectorOutputStyle;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors, OutputStyleInterface $rectorOutputStyle)
    {
        $this->rectors = $rectors;
        $this->rectorOutputStyle = $rectorOutputStyle;
    }
    public function reportIfMissing() : ?int
    {
        if ($this->filterActiveRectors($this->rectors) !== []) {
            return null;
        }
        $this->rectorOutputStyle->warning('We could not find any Rector rules to run. You have 2 options to add them:');
        $this->rectorOutputStyle->title('1. Add single rule to "rector.php"');
        $this->rectorOutputStyle->writeln('  $rectorConfig->rule(...);');
        $this->rectorOutputStyle->newLine(1);
        $this->rectorOutputStyle->title('2. Add set of rules to "rector.php"');
        $this->rectorOutputStyle->writeln('  $rectorConfig->sets([SetList::...]);');
        $this->rectorOutputStyle->newLine(1);
        $this->rectorOutputStyle->title('Missing "rector.php" in your project? Let Rector create it for you');
        $this->rectorOutputStyle->writeln('  vendor/bin/rector init');
        $this->rectorOutputStyle->newLine();
        return Command::FAILURE;
    }
    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    private function filterActiveRectors(array $rectors) : array
    {
        return \array_filter($rectors, static function (RectorInterface $rector) : bool {
            if ($rector instanceof PostRectorInterface) {
                return \false;
            }
            return !$rector instanceof ComplementaryRectorInterface;
        });
    }
}
