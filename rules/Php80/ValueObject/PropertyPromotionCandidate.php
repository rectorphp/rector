<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\ShouldNotHappenException;
final class PropertyPromotionCandidate
{
    /**
     * @readonly
     */
    private Property $property;
    /**
     * @readonly
     */
    private Param $param;
    /**
     * @readonly
     */
    private int $propertyStmtPosition;
    /**
     * @readonly
     */
    private int $constructorAssignStmtPosition;
    public function __construct(Property $property, Param $param, int $propertyStmtPosition, int $constructorAssignStmtPosition)
    {
        $this->property = $property;
        $this->param = $param;
        $this->propertyStmtPosition = $propertyStmtPosition;
        $this->constructorAssignStmtPosition = $constructorAssignStmtPosition;
    }
    public function getProperty(): Property
    {
        return $this->property;
    }
    public function getParam(): Param
    {
        return $this->param;
    }
    public function getParamName(): string
    {
        $paramVar = $this->param->var;
        if (!$paramVar instanceof Variable) {
            throw new ShouldNotHappenException();
        }
        if (!is_string($paramVar->name)) {
            throw new ShouldNotHappenException();
        }
        return $paramVar->name;
    }
    public function getPropertyStmtPosition(): int
    {
        return $this->propertyStmtPosition;
    }
    public function getConstructorAssignStmtPosition(): int
    {
        return $this->constructorAssignStmtPosition;
    }
}
