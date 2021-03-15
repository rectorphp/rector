<?php

declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
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
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
    }

    /**
     * @param Identical|NotIdentical $binaryOp
     */
    public function resolveBinaryOpForFunction(BinaryOp $binaryOp): ?ContentExprAndNeedleExpr
    {
        if ($binaryOp->left instanceof Variable) {
            return $this->matchContentExprAndNeedleExpr($binaryOp->right, $binaryOp->left);
        }

        if ($binaryOp->right instanceof Variable) {
            return $this->matchContentExprAndNeedleExpr($binaryOp->left, $binaryOp->right);
        }

        return null;
    }

    public function matchContentExprAndNeedleExpr(Node $node, Variable $variable): ?ContentExprAndNeedleExpr
    {
        if (! $this->nodeNameResolver->isFuncCallName($node, 'substr')) {
            return null;
        }

        /** @var FuncCall $node */
        if (! $node->args[1]->value instanceof UnaryMinus) {
            return null;
        }

        /** @var UnaryMinus $unaryMinus */
        $unaryMinus = $node->args[1]->value;

        if (! $this->nodeNameResolver->isFuncCallName($unaryMinus->expr, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $unaryMinus->expr;

        if ($this->nodeComparator->areNodesEqual($strlenFuncCall->args[0]->value, $variable)) {
            return new ContentExprAndNeedleExpr($node->args[0]->value, $strlenFuncCall->args[0]->value);
        }

        return null;
    }
}
