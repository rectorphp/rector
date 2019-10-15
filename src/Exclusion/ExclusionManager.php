<?php
declare(strict_types=1);

namespace Rector\Exclusion;

use PhpParser\Node;
use Rector\Contract\Exclusion\ExclusionCheckInterface;
use Rector\Contract\Rector\PhpRectorInterface;

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
        foreach ($this->exclusionChecks as $check) {
            if ($check->isNodeSkippedByRector($phpRector, $onNode)) {
                return true;
            }
        }
        return false;
    }
}
