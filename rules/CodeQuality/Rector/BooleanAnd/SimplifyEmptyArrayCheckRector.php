<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use Rector\NodeManipulator\BinaryOpManipulator;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector\SimplifyEmptyArrayCheckRectorTest
 */
final class SimplifyEmptyArrayCheckRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\BinaryOpManipulator
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
        $firstArgValue = $isArrayExpr->getArgs()[0]->value;
        /** @var Empty_ $emptyOrNotIdenticalNode */
        $emptyOrNotIdenticalNode = $twoNodeMatch->getSecondExpr();
        if ($emptyOrNotIdenticalNode->expr instanceof FuncCall && $this->nodeComparator->areNodesEqual($emptyOrNotIdenticalNode->expr->getArgs()[0]->value, $firstArgValue)) {
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
                return isset($node->getArgs()[0]);
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
                    return isset($node->expr->getArgs()[0]);
                }
                return \true;
            }
        );
    }
}
