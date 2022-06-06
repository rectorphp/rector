<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\GetClassToInstanceOfRector\GetClassToInstanceOfRectorTest
 */
final class GetClassToInstanceOfRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const NO_NAMESPACED_CLASSNAMES = ['self', 'static'];
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(\Rector\Core\NodeManipulator\BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes comparison with get_class to instanceof', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('if (EventsListener::class === get_class($event->job)) { }', 'if ($event->job instanceof EventsListener) { }')]);
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
            return $this->isClassReference($node);
        }, function (\PhpParser\Node $node) : bool {
            return $this->isGetClassFuncCallNode($node);
        });
        if (!$twoNodeMatch instanceof \Rector\Php71\ValueObject\TwoNodeMatch) {
            return null;
        }
        /** @var ClassConstFetch|String_ $firstExpr */
        $firstExpr = $twoNodeMatch->getFirstExpr();
        /** @var FuncCall $funcCall */
        $funcCall = $twoNodeMatch->getSecondExpr();
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $varNode = $funcCall->args[0]->value;
        if ($firstExpr instanceof \PhpParser\Node\Scalar\String_) {
            $className = $this->valueResolver->getValue($firstExpr);
        } else {
            $className = $this->getName($firstExpr->class);
        }
        if ($className === null) {
            return null;
        }
        if ($className === \Rector\Core\Enum\ObjectReference::PARENT) {
            return null;
        }
        $class = \in_array($className, self::NO_NAMESPACED_CLASSNAMES, \true) ? new \PhpParser\Node\Name($className) : new \PhpParser\Node\Name\FullyQualified($className);
        $instanceof = new \PhpParser\Node\Expr\Instanceof_($varNode, $class);
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
            return new \PhpParser\Node\Expr\BooleanNot($instanceof);
        }
        return $instanceof;
    }
    private function isClassReference(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            // might be
            return $node instanceof \PhpParser\Node\Scalar\String_;
        }
        return $this->isName($node->name, 'class');
    }
    private function isGetClassFuncCallNode(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        return $this->isName($node, 'get_class');
    }
}
