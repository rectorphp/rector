<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector;

trait DowngradeRectorTrait
{
    /**
     * Run the rector only when the feature is not supported
     */
    abstract protected function getPhpVersionFeature(): string;
}
