<?php
declare(strict_types=1);

namespace Rector\Contract\Exclusion;

use PhpParser\Node;
use Rector\Contract\Rector\PhpRectorInterface;

interface ExclusionCheckInterface
{
    public function shouldExcludeRector(PhpRectorInterface $phpRector, Node $onNode): bool;
}
