<?php

declare(strict_types=1);

namespace Rector\Downgrade\Contract\Rector;

interface DowngradeTypeRectorInterface
{
    /**
     * Name of the type to remove
     */
    public function getTypeNameToRemove(): string;
}
