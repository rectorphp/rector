<?php

declare(strict_types=1);

namespace Rector\Order\ValueObject;

use PhpParser\Node\Stmt\Property;
use Rector\Order\Contract\RankeableInterface;

final class PropertyRankeable implements RankeableInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $visibility;

    /**
     * @var int
     */
    private $position;

    /**
     * @var Property
     */
    private $property;

    public function __construct(string $name, int $visibility, Property $property, int $position)
    {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->property = $property;
        $this->position = $position;
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
