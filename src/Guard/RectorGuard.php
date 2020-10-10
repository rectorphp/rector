<?php

declare(strict_types=1);

namespace Rector\Core\Guard;

use Rector\Core\Application\ActiveRectorsProvider;
use Rector\Core\Exception\NoRectorsLoadedException;

final class RectorGuard
{
    /**
     * @var ActiveRectorsProvider
     */
    private $activeRectorsProvider;

    public function __construct(ActiveRectorsProvider $activeRectorsProvider)
    {
        $this->activeRectorsProvider = $activeRectorsProvider;
    }

    public function ensureSomeRectorsAreRegistered(): void
    {
        if ($this->activeRectorsProvider->provide() !== []) {
            return;
        }

        throw new NoRectorsLoadedException();
    }
}
