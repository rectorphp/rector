<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Exclusion;

use PhpParser\Node;
use Rector\Core\Contract\Rector\PhpRectorInterface;

interface ExclusionCheckInterface
{
    public function isNodeSkippedByRector(PhpRectorInterface $phpRector, Node $onNode): bool;
}
