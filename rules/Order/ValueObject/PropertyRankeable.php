<?php

declare (strict_types=1);
namespace Rector\Order\ValueObject;

use PhpParser\Node\Stmt\Property;
use Rector\Order\Contract\RankeableInterface;
final class PropertyRankeable implements \Rector\Order\Contract\RankeableInterface
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
     * @var \PhpParser\Node\Stmt\Property
     */
    private $property;
    /**
     * @var int
     */
    private $position;
    public function __construct(string $name, int $visibility, \PhpParser\Node\Stmt\Property $property, int $position)
    {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->property = $property;
        $this->position = $position;
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return bool[]|int[]
     */
    public function getRanks() : array
    {
        return [$this->visibility, $this->property->isStatic(), $this->position];
    }
}
