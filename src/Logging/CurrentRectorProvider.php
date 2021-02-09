<?php

declare(strict_types=1);

namespace Rector\Core\Logging;

use Rector\Core\Contract\Rector\RectorInterface;

final class CurrentRectorProvider
{
    /**
     * @var RectorInterface|null
     */
    private $currentRector;

    public function changeCurrentRector(RectorInterface $rector): void
    {
        $this->currentRector = $rector;
    }

    private function getCurrentRector(): ?RectorInterface
    {
        return $this->currentRector;
    }
}
