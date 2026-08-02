<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject\ValidatorAssert;

use PhpParser\Node\Expr\New_;
final class PropertyAndConstraint
{
    /**
     * @readonly
     */
    private string $property;
    /**
     * @readonly
     */
    private New_ $constraintNew;
    public function __construct(string $property, New_ $constraintNew)
    {
        $this->property = $property;
        $this->constraintNew = $constraintNew;
    }
    public function getProperty(): string
    {
        return $this->property;
    }
    public function getConstraintNew(): New_
    {
        return $this->constraintNew;
    }
}
