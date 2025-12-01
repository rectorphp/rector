<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Variable;
use RectorPrefix202512\Webmozart\Assert\Assert;
final class ConditionAndResult
{
    /**
     * @readonly
     */
    private Expr $conditionExpr;
    /**
     * @readonly
     */
    private Expr $resultExpr;
    public function __construct(Expr $conditionExpr, Expr $resultExpr)
    {
        $this->conditionExpr = $conditionExpr;
        $this->resultExpr = $resultExpr;
    }
    public function getConditionExpr(): Expr
    {
        return $this->conditionExpr;
    }
    public function isIdenticalCompare(): bool
    {
        return $this->conditionExpr instanceof Identical;
    }
    public function getIdenticalVariableName(): ?string
    {
        $identical = $this->getConditionIdentical();
        if (!$identical->left instanceof Variable) {
            return null;
        }
        $variable = $identical->left;
        if ($variable->name instanceof Expr) {
            return null;
        }
        return $variable->name;
    }
    public function getResultExpr(): Expr
    {
        return $this->resultExpr;
    }
    public function getIdenticalExpr(): Expr
    {
        /** @var Identical $identical */
        $identical = $this->conditionExpr;
        return $identical->right;
    }
    private function getConditionIdentical(): Identical
    {
        Assert::isInstanceOf($this->conditionExpr, Identical::class);
        return $this->conditionExpr;
    }
}
