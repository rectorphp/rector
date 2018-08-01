<?php declare(strict_types=1);

namespace Rector\Contract\Rector;

use Rector\RectorDefinition\RectorDefinition;

interface RectorInterface
{
    public function getDefinition(): RectorDefinition;
}
