<?php

declare(strict_types=1);

namespace Rector\Core\Exclusion;

use PhpParser\Node;
use Rector\Core\Contract\Exclusion\ExclusionCheckInterface;
use Rector\Core\Contract\Rector\PhpRectorInterface;

final class ExclusionManager
{
    /**
     * @var ExclusionCheckInterface[]
     */
    private $exclusionChecks = [];

    /**
     * @param ExclusionCheckInterface[] $exclusionChecks
     */
    public function __construct(array $exclusionChecks = [])
    {
        $this->exclusionChecks = $exclusionChecks;
    }

    public function isNodeSkippedByRector(PhpRectorInterface $phpRector, Node $onNode): bool
    {
        foreach ($this->exclusionChecks as $exclusionCheck) {
            if ($exclusionCheck->isNodeSkippedByRector($phpRector, $onNode)) {
                return true;
            }
        }

        return false;
    }
}
