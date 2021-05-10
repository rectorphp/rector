<?php

declare(strict_types=1);

namespace Rector\Order\ValueObject;

use PhpParser\Node\Stmt\Property;
use Rector\Order\Contract\RankeableInterface;

final class PropertyRankeable implements RankeableInterface
{
    public function __construct(
        private string $name,
        private int $visibility,
        private Property $property,
        private int $position
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool[]|int[]
     */
    public function getRanks(): array
    {
        return [$this->visibility, $this->property->isStatic(), $this->position];
    }
}
