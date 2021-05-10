<?php

declare(strict_types=1);

namespace Rector\Order\ValueObject;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\Order\Contract\RankeableInterface;

final class ClassMethodRankeable implements RankeableInterface
{
    public function __construct(
        private string $name,
        private int $visibility,
        private int $position,
        private ClassMethod $classMethod
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * An array to sort the element order by
     * @return bool[]|int[]
     */
    public function getRanks(): array
    {
        return [
            $this->visibility,
            $this->classMethod->isStatic(),
            // negated on purpose, to put abstract later
            ! $this->classMethod->isAbstract(),
            $this->classMethod->isFinal(),
            $this->position,
        ];
    }
}
