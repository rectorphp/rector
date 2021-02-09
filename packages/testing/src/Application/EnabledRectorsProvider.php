<?php

declare(strict_types=1);

namespace Rector\Testing\Application;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;

final class EnabledRectorsProvider
{
    /**
     * @var class-string<RectorInterface>|null
     */
    private $enabledRectorClass;

    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function setEnabledRector(string $rectorClass): void
    {
        $this->enabledRectorClass = $rectorClass;
    }

    public function isConfigured(): bool
    {
        return $this->enabledRectorClass !== null;
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getEnabledRector(): string
    {
        if ($this->enabledRectorClass === null) {
            throw new ShouldNotHappenException();
        }

        return $this->enabledRectorClass;
    }

    public function reset(): void
    {
        $this->enabledRectorClass = null;
    }
}
