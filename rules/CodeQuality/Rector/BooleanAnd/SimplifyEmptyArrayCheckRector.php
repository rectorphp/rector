<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/EZ2P4
 * @changelog https://3v4l.org/egtb5
 * @see \Rector\Tests\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector\SimplifyEmptyArrayCheckRectorTest
 */
final class SimplifyEmptyArrayCheckRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array', [new CodeSample('is_array($values) && empty($values)', '$values === []')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [BooleanAnd::class];
    }
    /**
     * @param BooleanAnd $node
     */
    public function refactor(Node $node) : ?Node
    {
        $twoNodeMatch = $this->resolvetwoNodeMatch($node);
        if (!$twoNodeMatch instanceof TwoNodeMatch) {
            return null;
        }
        /** @var FuncCall $isArrayExpr */
        $isArrayExpr = $twoNodeMatch->getFirstExpr();
        /** @var Expr $firstArgValue */
        $firstArgValue = $isArrayExpr->args[0]->value;
        /** @var Empty_ $emptyOrNotIdenticalNode */
        $emptyOrNotIdenticalNode = $twoNodeMatch->getSecondExpr();
        if ($emptyOrNotIdenticalNode->expr instanceof FuncCall && $this->nodeComparator->areNodesEqual($emptyOrNotIdenticalNode->expr->args[0]->value, $firstArgValue)) {
            return new Identical($emptyOrNotIdenticalNode->expr, new Array_());
        }
        if (!$this->nodeComparator->areNodesEqual($emptyOrNotIdenticalNode->expr, $firstArgValue)) {
            return null;
        }
        return new Identical($emptyOrNotIdenticalNode->expr, new Array_());
    }
    private function resolvetwoNodeMatch(BooleanAnd $booleanAnd) : ?TwoNodeMatch
    {
        return $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $booleanAnd,
            // is_array(...)
            function (Node $node) : bool {
                if (!$node instanceof FuncCall) {
                    return \false;
                }
                if ($node->isFirstClassCallable()) {
                    return \false;
                }
                if (!$this->isName($node, 'is_array')) {
                    return \false;
                }
                return isset($node->args[0]);
            },
            // empty(...)
            function (Node $node) : bool {
                if (!$node instanceof Empty_) {
                    return \false;
                }
                if ($node->expr instanceof FuncCall) {
                    if ($node->expr->isFirstClassCallable()) {
                        return \false;
                    }
                    if (!$this->isName($node->expr, 'array_filter')) {
                        return \false;
                    }
                    return isset($node->expr->args[0]);
                }
                return \true;
            }
        );
    }
}
