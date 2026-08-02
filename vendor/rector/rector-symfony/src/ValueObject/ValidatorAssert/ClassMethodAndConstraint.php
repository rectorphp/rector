<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject\ValidatorAssert;

use PhpParser\Node\Expr\New_;
final class ClassMethodAndConstraint
{
    /**
     * @var string[]
     * @readonly
     */
    private array $possibleMethodNames;
    /**
     * @readonly
     */
    private New_ $constraintNew;
    /**
     * @param string[] $possibleMethodNames
     */
    public function __construct(array $possibleMethodNames, New_ $constraintNew)
    {
        $this->possibleMethodNames = $possibleMethodNames;
        $this->constraintNew = $constraintNew;
    }
    /**
     * @return string[]
     */
    public function getPossibleMethodNames(): array
    {
        return $this->possibleMethodNames;
    }
    public function getConstraintNew(): New_
    {
        return $this->constraintNew;
    }
}
