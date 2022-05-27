<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Nette\ValueObject\ContentExprAndNeedleExpr;
use Rector\NodeNameResolver\NodeNameResolver;
final class StrlenEndsWithResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     */
    public function resolveBinaryOpForFunction($binaryOp) : ?\Rector\Nette\ValueObject\ContentExprAndNeedleExpr
    {
        if ($binaryOp->left instanceof \PhpParser\Node\Expr\Variable) {
            return $this->matchContentExprAndNeedleExpr($binaryOp->right, $binaryOp->left);
        }
        if ($binaryOp->right instanceof \PhpParser\Node\Expr\Variable) {
            return $this->matchContentExprAndNeedleExpr($binaryOp->left, $binaryOp->right);
        }
        return null;
    }
    public function matchContentExprAndNeedleExpr(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable) : ?\Rector\Nette\ValueObject\ContentExprAndNeedleExpr
    {
        if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($node, 'substr')) {
            return null;
        }
        /** @var FuncCall $node */
        if (!$node->args[1]->value instanceof \PhpParser\Node\Expr\UnaryMinus) {
            return null;
        }
        /** @var UnaryMinus $unaryMinus */
        $unaryMinus = $node->args[1]->value;
        if (!$unaryMinus->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($unaryMinus->expr, 'strlen')) {
            return null;
        }
        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $unaryMinus->expr;
        if ($this->nodeComparator->areNodesEqual($strlenFuncCall->args[0]->value, $variable)) {
            return new \Rector\Nette\ValueObject\ContentExprAndNeedleExpr($node->args[0]->value, $strlenFuncCall->args[0]->value);
        }
        return null;
    }
}
