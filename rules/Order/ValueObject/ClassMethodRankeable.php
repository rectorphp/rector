<?php

declare (strict_types=1);
namespace Rector\Order\ValueObject;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\Order\Contract\RankeableInterface;
final class ClassMethodRankeable implements \Rector\Order\Contract\RankeableInterface
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
     * @var \PhpParser\Node\Stmt\ClassMethod
     */
    private $classMethod;
    public function __construct(string $name, int $visibility, int $position, \PhpParser\Node\Stmt\ClassMethod $classMethod)
    {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->position = $position;
        $this->classMethod = $classMethod;
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * An array to sort the element order by
     * @return bool[]|int[]
     */
    public function getRanks() : array
    {
        return [
            $this->visibility,
            $this->classMethod->isStatic(),
            // negated on purpose, to put abstract later
            !$this->classMethod->isAbstract(),
            $this->classMethod->isFinal(),
            $this->position,
        ];
    }
}
