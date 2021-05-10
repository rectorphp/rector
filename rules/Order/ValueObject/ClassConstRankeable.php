<?php

declare (strict_types=1);
namespace Rector\Order\ValueObject;

use Rector\Order\Contract\RankeableInterface;
final class ClassConstRankeable implements \Rector\Order\Contract\RankeableInterface
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
    public function __construct(string $name, int $visibility, int $position)
    {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->position = $position;
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * An array to sort the element order by
     * @return int[]
     */
    public function getRanks() : array
    {
        return [$this->visibility, $this->position];
    }
}
