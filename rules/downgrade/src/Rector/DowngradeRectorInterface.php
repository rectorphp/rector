<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector;

interface DowngradeRectorInterface
{
    /**
     * Run the rector only when the feature is not supported
     */
    function getPhpVersionFeature(): string;
}
