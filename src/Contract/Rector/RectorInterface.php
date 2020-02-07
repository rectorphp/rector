<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Rector;

use Rector\Core\RectorDefinition\RectorDefinition;

interface RectorInterface
{
    public function getDefinition(): RectorDefinition;
}
