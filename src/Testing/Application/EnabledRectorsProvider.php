<?php

declare(strict_types=1);

namespace Rector\Core\Testing\Application;

use Rector\Core\Configuration\ChangeConfiguration;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\Renaming\Rector\Name\RenameClassRector;

final class EnabledRectorsProvider
{
    /**
     * @var mixed[][]
     */
    private $enabledRectorsWithConfiguration = [];

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
        $this->enabledRectorsWithConfiguration[$rector] = $configuration;

        if (StaticPHPUnitEnvironment::isPHPUnitRun() && is_a($rector, RenameClassRector::class, true)) {
            // only in unit tests
            $this->changeConfiguration->setOldToNewClasses($configuration[RenameClassRector::OLD_TO_NEW_CLASSES] ?? []);
        }
    }

    public function reset(): void
    {
        $this->enabledRectorsWithConfiguration = [];
    }

    public function isConfigured(): bool
    {
        return (bool) $this->enabledRectorsWithConfiguration;
    }

    /**
     * @return mixed[][]
     */
    public function getEnabledRectors(): array
    {
        return $this->enabledRectorsWithConfiguration;
    }

    public function isRectorActive(RectorInterface $rector): bool
    {
        if (! $this->isConfigured()) {
            return true;
        }

        foreach (array_keys($this->enabledRectorsWithConfiguration) as $rectorClass) {
            if (is_a($rector, $rectorClass, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed[]
     */
    public function getRectorConfiguration(RectorInterface $rector): array
    {
        foreach ($this->enabledRectorsWithConfiguration as $rectorClass => $configuration) {
            if (! is_a($rector, $rectorClass, true)) {
                continue;
            }

            return $configuration;
        }

        return [];
    }
}
