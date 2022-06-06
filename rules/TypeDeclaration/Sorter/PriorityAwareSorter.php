<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Sorter;

use Rector\TypeDeclaration\Contract\PriorityAwareInterface;
use Rector\TypeDeclaration\Exception\ConflictingPriorityException;
final class PriorityAwareSorter
{
    /**
     * @template TPriorityAware as PriorityAwareInterface
     * @param TPriorityAware[] $priorityAwares
     * @return TPriorityAware[]
     */
    public function sort(array $priorityAwares) : array
    {
        $sortedTypeInferers = [];
        foreach ($priorityAwares as $priorityAware) {
            $this->ensurePriorityIsUnique($sortedTypeInferers, $priorityAware);
            $sortedTypeInferers[$priorityAware->getPriority()] = $priorityAware;
        }
        \krsort($sortedTypeInferers);
        return $sortedTypeInferers;
    }
    /**
     * @param PriorityAwareInterface[] $sortedTypeInferers
     */
    private function ensurePriorityIsUnique(array $sortedTypeInferers, \Rector\TypeDeclaration\Contract\PriorityAwareInterface $priorityAware) : void
    {
        if (!isset($sortedTypeInferers[$priorityAware->getPriority()])) {
            return;
        }
        $alreadySetPropertyTypeInferer = $sortedTypeInferers[$priorityAware->getPriority()];
        throw new \Rector\TypeDeclaration\Exception\ConflictingPriorityException($priorityAware, $alreadySetPropertyTypeInferer);
    }
}
