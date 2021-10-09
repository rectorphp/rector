<?php

declare(strict_types=1);

namespace Rector\Core\Validation;

use Nette\Utils\Strings;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class EmptyConfigurableRectorChecker
{
    /**
     * @var string
     */
    private const SOLUTION_MESSAGE = 'Do you want to run them? Ensure configure them in your `rector.php`.';

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
        $this->reportEmptyConfigurableMessage($emptyConfigurableRectors);
        $this->symfonyStyle->note(self::SOLUTION_MESSAGE);

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
            'Your project contains %d configurable rector rules that skipped as need to be configured to run, use -vvv for detailed info.',
            count($emptyConfigurableRectors)
        );

        $this->symfonyStyle->warning($warningMessage);
    }

    /**
     * @param RectorInterface[] $emptyConfigurableRectors
     */
    private function reportEmptyConfigurableMessage(array $emptyConfigurableRectors): void
    {
        if (! $this->symfonyStyle->isVerbose()) {
            return;
        }

        foreach ($emptyConfigurableRectors as $emptyConfigurableRector) {
            $shortRectorClass = Strings::after($emptyConfigurableRector::class, '\\', -1);

            $rectorMessage = ' * ' . $shortRectorClass;
            $this->symfonyStyle->writeln($rectorMessage);
        }
    }
}
