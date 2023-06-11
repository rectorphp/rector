<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
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
    public function getStmtPosition() : int
    {
        return $this->expression->getAttribute(AttributeKey::STMT_KEY);
    }
}
