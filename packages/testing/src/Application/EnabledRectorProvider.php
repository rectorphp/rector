<?php

declare(strict_types=1);

namespace Rector\Testing\Application;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;

final class EnabledRectorProvider
{
    /**
     * @var class-string<RectorInterface>|null
     */
    private $enabledRector;

    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function setEnabledRector(string $rectorClass): void
    {
        $this->enabledRector = $rectorClass;
    }

    public function reset(): void
    {
        $this->enabledRector = null;
    }

    public function isConfigured(): bool
    {
        return $this->enabledRector !== null;
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getEnabledRector(): string
    {
        if ($this->enabledRector === null) {
            throw new ShouldNotHappenException();
        }

        return $this->enabledRector;
    }
}
