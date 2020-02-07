<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use Rector\TypeDeclaration\Contract\TypeInferer\PriorityAwareTypeInfererInterface;
use Rector\TypeDeclaration\Exception\ConflictingPriorityException;

abstract class AbstractPriorityAwareTypeInferer
{
    /**
     * @var PriorityAwareTypeInfererInterface[]
     */
    private $sortedTypeInferers = [];

    /**
     * @param PriorityAwareTypeInfererInterface[] $priorityAwareTypeInferers
     * @return PriorityAwareTypeInfererInterface[]
     */
    protected function sortTypeInferersByPriority(array $priorityAwareTypeInferers): array
    {
        $this->sortedTypeInferers = [];

        foreach ($priorityAwareTypeInferers as $propertyTypeInferer) {
            $this->ensurePriorityIsUnique($propertyTypeInferer);
            $this->sortedTypeInferers[$propertyTypeInferer->getPriority()] = $propertyTypeInferer;
        }

        krsort($this->sortedTypeInferers);

        return $this->sortedTypeInferers;
    }

    private function ensurePriorityIsUnique(PriorityAwareTypeInfererInterface $priorityAwareTypeInferer): void
    {
        if (! isset($this->sortedTypeInferers[$priorityAwareTypeInferer->getPriority()])) {
            return;
        }

        $alreadySetPropertyTypeInferer = $this->sortedTypeInferers[$priorityAwareTypeInferer->getPriority()];

        throw new ConflictingPriorityException($priorityAwareTypeInferer, $alreadySetPropertyTypeInferer);
    }
}
