<?php

declare(strict_types=1);

namespace Rector\Core\Testing\Application;

final class EnabledRectorsProvider
{
    /**
     * @var mixed[][]
     */
    private $enabledRectors = [];

    /**
     * @param mixed[] $configuration
     */
    public function addEnabledRector(string $rector, array $configuration = []): void
    {
        $this->enabledRectors[$rector] = $configuration;
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
