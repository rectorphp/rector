<?php

declare (strict_types=1);
namespace Rector\Php81\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\NodeAnalyzer\ExprAnalyzer;
final class ComplexNewAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function isDynamic(New_ $new) : bool
    {
        if (!$new->class instanceof FullyQualified) {
            return \true;
        }
        if ($new->isFirstClassCallable()) {
            return \false;
        }
        $args = $new->getArgs();
        foreach ($args as $arg) {
            $value = $arg->value;
            if ($this->isAllowedNew($value)) {
                continue;
            }
            // new inside array is allowed for New in initializer
            if ($value instanceof Array_ && $this->isAllowedArray($value)) {
                continue;
            }
            if (!$this->exprAnalyzer->isDynamicExpr($value)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    private function isAllowedNew(Expr $expr) : bool
    {
        if ($expr instanceof New_) {
            return !$this->isDynamic($expr);
        }
        return \false;
    }
    private function isAllowedArray(Array_ $array) : bool
    {
        if (!$this->exprAnalyzer->isDynamicArray($array)) {
            return \true;
        }
        $arrayItems = $array->items;
        foreach ($arrayItems as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->value instanceof New_) {
                return \false;
            }
            if ($this->isDynamic($arrayItem->value)) {
                return \false;
            }
        }
        return \true;
    }
}
