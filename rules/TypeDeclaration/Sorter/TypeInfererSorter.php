<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Sorter;

use Rector\TypeDeclaration\Contract\TypeInferer\PriorityAwareTypeInfererInterface;
use Rector\TypeDeclaration\Exception\ConflictingPriorityException;
final class TypeInfererSorter
{
    /**
     * @param PriorityAwareTypeInfererInterface[] $priorityAwareTypeInferers
     * @return PriorityAwareTypeInfererInterface[]
     */
    public function sort(array $priorityAwareTypeInferers) : array
    {
        $sortedTypeInferers = [];
        foreach ($priorityAwareTypeInferers as $priorityAwareTypeInferer) {
            $this->ensurePriorityIsUnique($sortedTypeInferers, $priorityAwareTypeInferer);
            $sortedTypeInferers[$priorityAwareTypeInferer->getPriority()] = $priorityAwareTypeInferer;
        }
        \krsort($sortedTypeInferers);
        return $sortedTypeInferers;
    }
    /**
     * @param PriorityAwareTypeInfererInterface[] $sortedTypeInferers
     */
    private function ensurePriorityIsUnique(array $sortedTypeInferers, \Rector\TypeDeclaration\Contract\TypeInferer\PriorityAwareTypeInfererInterface $priorityAwareTypeInferer) : void
    {
        if (!isset($sortedTypeInferers[$priorityAwareTypeInferer->getPriority()])) {
            return;
        }
        $alreadySetPropertyTypeInferer = $sortedTypeInferers[$priorityAwareTypeInferer->getPriority()];
        throw new \Rector\TypeDeclaration\Exception\ConflictingPriorityException($priorityAwareTypeInferer, $alreadySetPropertyTypeInferer);
    }
}
