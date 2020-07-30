<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Rector;

interface ConfigurableRectorInterface
{
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void;
}
