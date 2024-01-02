<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PropertyPromotionCandidate
{
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\Property
     */
    private $property;
    /**
     * @readonly
     * @var \PhpParser\Node\Param
     */
    private $param;
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\Expression
     */
    private $expression;
    public function __construct(Property $property, Param $param, Expression $expression)
    {
        $this->property = $property;
        $this->param = $param;
        $this->expression = $expression;
    }
    public function getProperty() : Property
    {
        return $this->property;
    }
    public function getParam() : Param
    {
        return $this->param;
    }
    public function getParamName() : string
    {
        $paramVar = $this->param->var;
        if (!$paramVar instanceof Variable) {
            throw new ShouldNotHappenException();
        }
        if (!\is_string($paramVar->name)) {
            throw new ShouldNotHappenException();
        }
        return $paramVar->name;
    }
    public function getStmtPosition() : int
    {
        return $this->expression->getAttribute(AttributeKey::STMT_KEY);
    }
}
