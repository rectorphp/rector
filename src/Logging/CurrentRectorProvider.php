<?php

declare(strict_types=1);

namespace Rector\Core\Logging;

use Rector\Core\Contract\Rector\RectorInterface;

final class CurrentRectorProvider
{
    private ?RectorInterface $currentRector = null;

    public function changeCurrentRector(RectorInterface $rector): void
    {
        $this->currentRector = $rector;
    }

    public function getCurrentRector(): ?RectorInterface
    {
        return $this->currentRector;
    }
}
