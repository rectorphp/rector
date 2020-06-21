<?php

declare(strict_types=1);

namespace Rector\Core\Testing\Application;

use Rector\Core\Configuration\ChangeConfiguration;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\Renaming\Rector\Class_\RenameClassRector;

final class EnabledRectorsProvider
{
    /**
     * @var mixed[][]
     */
    private $enabledRectors = [];

    /**
     * @var ChangeConfiguration
     */
    private $changeConfiguration;

    public function __construct(ChangeConfiguration $changeConfiguration)
    {
        $this->changeConfiguration = $changeConfiguration;
    }

    /**
     * @param mixed[] $configuration
     */
    public function addEnabledRector(string $rector, array $configuration = []): void
    {
        $this->enabledRectors[$rector] = $configuration;

        if (StaticPHPUnitEnvironment::isPHPUnitRun() && is_a($rector, RenameClassRector::class, true)) {
            // only in unit tests
            $this->changeConfiguration->setOldToNewClasses($configuration['$oldToNewClasses'] ?? []);
        }
    }

    public function reset(): void
    {
        $this->enabledRectors = [];
    }

    public function isEnabled(): bool
    {
        return (bool) $this->enabledRectors;
    }

    /**
     * @return string[][]
     */
    public function getEnabledRectors(): array
    {
        return $this->enabledRectors;
    }
}
