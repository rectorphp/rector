<?php

declare (strict_types=1);
namespace Rector\Core\Validation;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle;
final class EmptyConfigurableRectorChecker
{
    /**
     * @readonly
     * @var \Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector
     */
    private $emptyConfigurableRectorCollector;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(\Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector $emptyConfigurableRectorCollector, \RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->emptyConfigurableRectorCollector = $emptyConfigurableRectorCollector;
        $this->symfonyStyle = $symfonyStyle;
    }
    public function check() : void
    {
        $emptyConfigurableRectorClasses = $this->emptyConfigurableRectorCollector->resolveEmptyConfigurableRectorClasses();
        if ($emptyConfigurableRectorClasses === []) {
            return;
        }
        $this->reportWarningMessage($emptyConfigurableRectorClasses);
        $solutionMessage = \sprintf('Do you want to run them?%sConfigure them in `rector.php` with "...->configure(...);"', \PHP_EOL);
        $this->symfonyStyle->note($solutionMessage);
        if (!$this->symfonyStyle->isVerbose()) {
            // ensure there is new line after progress bar and report : "[OK] Rector is done!" with add a space
            $this->symfonyStyle->write(' ');
        }
    }
    /**
     * @param array<class-string<ConfigurableRectorInterface>> $emptyConfigurableRectorClasses
     */
    private function reportWarningMessage(array $emptyConfigurableRectorClasses) : void
    {
        $warningMessage = \sprintf('Your project contains %d configurable rector rules that are skipped as need to be configured to run.', \count($emptyConfigurableRectorClasses));
        $this->symfonyStyle->warning($warningMessage);
        foreach ($emptyConfigurableRectorClasses as $emptyConfigurableRectorClass) {
            $this->symfonyStyle->writeln(' * ' . $emptyConfigurableRectorClass);
        }
        // to take time to absorb it
        \sleep(3);
    }
}
