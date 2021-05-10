<?php

declare(strict_types=1);

namespace Rector\Order\ValueObject;

use Rector\Order\Contract\RankeableInterface;

final class ClassConstRankeable implements RankeableInterface
{
    public function __construct(
        private string $name,
        private int $visibility,
        private int $position
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * An array to sort the element order by
     * @return int[]
     */
    public function getRanks(): array
    {
        return [$this->visibility, $this->position];
    }
}
