<?php

declare(strict_types=1);

namespace Rector\Core\Validation;

use function count;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class EmptyConfigurableRectorChecker
{
    public function __construct(
        private EmptyConfigurableRectorCollector $emptyConfigurableRectorCollector,
        private SymfonyStyle $symfonyStyle
    ) {
    }

    /**
     * @param RectorInterface[] $rectors
     */
    public function check(array $rectors): void
    {
        $emptyConfigurableRectors = $this->emptyConfigurableRectorCollector->resolveEmptyConfigurable($rectors);
        if ($emptyConfigurableRectors === []) {
            return;
        }

        $this->reportWarningMessage($emptyConfigurableRectors);

        $solutionMessage = sprintf(
            'Do you want to run them?%sConfigure them in `rector.php` with ...->call("configure", ...);',
            PHP_EOL
        );
        $this->symfonyStyle->note($solutionMessage);

        if (! $this->symfonyStyle->isVerbose()) {
            // ensure there is new line after progress bar and report : "[OK] Rector is done!" with add a space
            $this->symfonyStyle->write(' ');
        }
    }

    /**
     * @param RectorInterface[] $emptyConfigurableRectors
     */
    private function reportWarningMessage(array $emptyConfigurableRectors): void
    {
        $warningMessage = sprintf(
            'Your project contains %d configurable rector rules that are skipped as need to be configured to run.',
            count($emptyConfigurableRectors)
        );
        $this->symfonyStyle->warning($warningMessage);

        foreach ($emptyConfigurableRectors as $emptyConfigurableRector) {
            $this->symfonyStyle->writeln(' * ' . $emptyConfigurableRector::class);
        }

        // to take time to absorb it
        sleep(3);
    }
}
