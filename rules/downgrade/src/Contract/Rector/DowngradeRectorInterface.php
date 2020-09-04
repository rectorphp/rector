<?php

declare(strict_types=1);

namespace Rector\Downgrade\Contract\Rector;

interface DowngradeRectorInterface
{
    /**
     * Run the rector only when the feature is not supported
     */
    public function getPhpVersionFeature(): string;
}
