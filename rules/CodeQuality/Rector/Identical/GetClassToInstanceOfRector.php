<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\Rector\Identical;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Instanceof_;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\Enum\ObjectReference;
use RectorPrefix20220606\Rector\Core\NodeManipulator\BinaryOpManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Php71\ValueObject\TwoNodeMatch;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\GetClassToInstanceOfRector\GetClassToInstanceOfRectorTest
 */
final class GetClassToInstanceOfRector extends AbstractRector
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
    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes comparison with get_class to instanceof', [new CodeSample('if (EventsListener::class === get_class($event->job)) { }', 'if ($event->job instanceof EventsListener) { }')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($node, function (Node $node) : bool {
            return $this->isClassReference($node);
        }, function (Node $node) : bool {
            return $this->isGetClassFuncCallNode($node);
        });
        if (!$twoNodeMatch instanceof TwoNodeMatch) {
            return null;
        }
        /** @var ClassConstFetch|String_ $firstExpr */
        $firstExpr = $twoNodeMatch->getFirstExpr();
        /** @var FuncCall $funcCall */
        $funcCall = $twoNodeMatch->getSecondExpr();
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof Arg) {
            return null;
        }
        $varNode = $funcCall->args[0]->value;
        if ($firstExpr instanceof String_) {
            $className = $this->valueResolver->getValue($firstExpr);
        } else {
            $className = $this->getName($firstExpr->class);
        }
        if ($className === null) {
            return null;
        }
        if ($className === ObjectReference::PARENT) {
            return null;
        }
        $class = \in_array($className, self::NO_NAMESPACED_CLASSNAMES, \true) ? new Name($className) : new FullyQualified($className);
        $instanceof = new Instanceof_($varNode, $class);
        if ($node instanceof NotIdentical) {
            return new BooleanNot($instanceof);
        }
        return $instanceof;
    }
    private function isClassReference(Node $node) : bool
    {
        if (!$node instanceof ClassConstFetch) {
            // might be
            return $node instanceof String_;
        }
        return $this->isName($node->name, 'class');
    }
    private function isGetClassFuncCallNode(Node $node) : bool
    {
        if (!$node instanceof FuncCall) {
            return \false;
        }
        return $this->isName($node, 'get_class');
    }
}
