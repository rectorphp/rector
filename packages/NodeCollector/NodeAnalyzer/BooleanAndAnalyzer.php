<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
final class BooleanAndAnalyzer
{
    /**
     * @return Expr[]
     */
    public function findBooleanAndConditions(\PhpParser\Node\Expr\BinaryOp\BooleanAnd $booleanAnd) : array
    {
        $conditions = [];
        while ($booleanAnd instanceof \PhpParser\Node\Expr\BinaryOp) {
            $conditions[] = $booleanAnd->right;
            $booleanAnd = $booleanAnd->left;
            if (!$booleanAnd instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
                $conditions[] = $booleanAnd;
                break;
            }
        }
        \krsort($conditions);
        return $conditions;
    }
}
