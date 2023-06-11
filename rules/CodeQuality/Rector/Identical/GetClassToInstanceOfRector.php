<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
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
final class GetClassToInstanceOfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    /**
     * @var string[]
     */
    private const NO_NAMESPACED_CLASSNAMES = ['self', 'static'];
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
        /** @var FuncCall $secondExpr */
        $secondExpr = $twoNodeMatch->getSecondExpr();
        if ($secondExpr->isFirstClassCallable()) {
            return null;
        }
        if (!isset($secondExpr->getArgs()[0])) {
            return null;
        }
        $firstArg = $secondExpr->getArgs()[0];
        $varNode = $firstArg->value;
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
