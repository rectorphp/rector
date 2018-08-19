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
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyReturnsRector extends AbstractRector
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $ifNode
     */
    public function refactor(Node $ifNode): ?Node
    {
        $condition = $ifNode->cond;

        if (! in_array(get_class($condition), $this->binaryOperations, true)) {
            return null;
        }

        $nextNode = $condition->getAttribute(Attribute::NEXT_NODE);

        if (! $nextNode instanceof Return_) {
            return null;
        }

        $returnNode = $nextNode;

        $returnExpr = $returnNode->expr;

        if (! $returnExpr instanceof ConstFetch) {
            return null;
        }

        $constFetchNode = $returnExpr;

        if ($constFetchNode->name->toLowerString() !== 'true') {
            return null;
        }

        $nextNodeAfterReturn = $returnNode->getAttribute(Attribute::NEXT_NODE);
        $nextNodeAfterIf = $ifNode->getAttribute(Attribute::NEXT_NODE);

        if (! $nextNodeAfterReturn instanceof Else_ && !$nextNodeAfterIf instanceof Return_) {
            return null;
        }

        if ($nextNodeAfterIf !== null) {
            $returnExpr = $nextNodeAfterIf->expr;
            if (! $returnExpr instanceof ConstFetch) {
                return null;
            }

            $constFetchNode = $returnExpr;

            if ($constFetchNode->name->toLowerString() !== 'false') {
                return null;
            }

            // continue
        }

        $nextNode = $nextNodeAfterReturn;

        $elseExpr = $nextNode->stmts[0];

        if (! $elseExpr instanceof Return_) {
            return null;
        }

        $returnExpr = $elseExpr->expr;

        if (! $returnExpr instanceof ConstFetch) {
            return null;
        }

        $constFetchNode = $returnExpr;

        if ($constFetchNode->name->toLowerString() !== 'false') {
            return null;
        }

        $nextPossibleNode = $ifNode->getAttribute(Attribute::NEXT_NODE);

        if ($nextPossibleNode !== null) {
            // remove next node
        }

        return new Return_($condition);
    }
}
