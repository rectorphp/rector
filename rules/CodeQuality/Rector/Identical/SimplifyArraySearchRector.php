<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\SimplifyArraySearchRector\SimplifyArraySearchRectorTest
 */
final class SimplifyArraySearchRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(\Rector\Core\NodeManipulator\BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify array_search to in_array', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('array_search("searching", $array) !== false;', 'in_array("searching", $array);'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('array_search("searching", $array, true) !== false;', 'in_array("searching", $array, true);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class, \PhpParser\Node\Expr\BinaryOp\NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($node, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, 'array_search');
        }, function (\PhpParser\Node $node) : bool {
            return $this->valueResolver->isFalse($node);
        });
        if (!$twoNodeMatch instanceof \Rector\Php71\ValueObject\TwoNodeMatch) {
            return null;
        }
        /** @var FuncCall $arraySearchFuncCall */
        $arraySearchFuncCall = $twoNodeMatch->getFirstExpr();
        $inArrayFuncCall = $this->nodeFactory->createFuncCall('in_array', $arraySearchFuncCall->args);
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return new \PhpParser\Node\Expr\BooleanNot($inArrayFuncCall);
        }
        return $inArrayFuncCall;
    }
}
