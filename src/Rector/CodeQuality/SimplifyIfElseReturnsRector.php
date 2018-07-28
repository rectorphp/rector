<?php declare(strict_types=1);

namespace Rector\Rector\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyIfElseReturnsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $binaryOperations = [
        Identical::class,
        NotIdentical::class,
        Equal::class,
        NotEqual::class,
        Greater::class,
        Smaller::class,
        GreaterOrEqual::class,
        SmallerOrEqual::class,
    ];

    /**
     * @var BinaryOp
     */
    private $condition;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Symplify if/else conditions that return something.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
if ($something === 1) {
    return true;
} else {
    return false;
}
CODE_SAMPLE
                ,
                'return $something === 1;'
            )]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof If_) {
            return false;
        }

        $condition = $node->cond;

        if (! in_array(get_class($condition), $this->binaryOperations, true)) {
            return false;
        }

        /** @var BinaryOp $binaryOperation */
        $binaryOperation = $condition;

        $this->condition = $binaryOperation;

        $nextNode = $this->condition->getAttribute(Attribute::NEXT_NODE);

        if (! $nextNode instanceof Return_) {
            return false;
        }

        $returnNode = $nextNode;

        $returnExpr = $returnNode->expr;

        if (! $returnExpr instanceof ConstFetch) {
            return false;
        }

        $constFetchNode = $returnExpr;

        if ($constFetchNode->name->toLowerString() !== 'true') {
            return false;
        }

        $nextNode = $returnNode->getAttribute(Attribute::NEXT_NODE);

        if (! $nextNode instanceof Else_) {
            return false;
        }

        $elseNode = $nextNode;

        $elseExpr = $elseNode->stmts[0];

        if (! $elseExpr instanceof Return_) {
            return false;
        }

        $returnExpr = $elseExpr->expr;

        if (! $returnExpr instanceof ConstFetch) {
            return false;
        }

        $constFetchNode = $returnExpr;

        return $constFetchNode->name->toLowerString() === 'false';
    }

    public function refactor(Node $node): ?Node
    {
        return new Return_($this->condition);
    }
}
