<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\RemoveParentCallWithoutParentRectorTest
 */
final class RemoveParentCallWithoutParentRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassMethodManipulator
     */
    private $classMethodManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ClassMethodManipulator $classMethodManipulator, ClassAnalyzer $classAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->classAnalyzer = $classAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused parent call with no parent class', [new CodeSample(<<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
         parent::__construct();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $class = $node;
        foreach ($node->getMethods() as $classMethod) {
            $this->traverseNodesWithCallable($classMethod, function (Node $node) use($class) {
                // skip nested anonmyous class
                if ($node instanceof Class_) {
                    return NodeTraverser::STOP_TRAVERSAL;
                }
                if ($node instanceof Assign) {
                    return $this->refactorAssign($node, $class);
                }
                if ($node instanceof Expression) {
                    $this->refactorExpression($node, $class);
                    return null;
                }
                return null;
            });
        }
        return null;
    }
    public function refactorAssign(Assign $assign, Class_ $class) : ?Assign
    {
        if (!$this->isParentStaticCall($assign->expr)) {
            return null;
        }
        /** @var StaticCall $staticCall */
        $staticCall = $assign->expr;
        // is valid call
        if ($this->doesCalledMethodExistInParent($staticCall, $class)) {
            return null;
        }
        $assign->expr = $this->nodeFactory->createNull();
        return $assign;
    }
    private function isParentStaticCall(Expr $expr) : bool
    {
        if (!$expr instanceof StaticCall) {
            return \false;
        }
        if (!$expr->class instanceof Name) {
            return \false;
        }
        return $this->isName($expr->class, ObjectReference::PARENT);
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if ($class->extends instanceof FullyQualified && !$this->reflectionProvider->hasClass($class->extends->toString())) {
            return \true;
        }
        // currently the classMethodManipulator isn't able to find usages of anonymous classes
        return $this->classAnalyzer->isAnonymousClass($class);
    }
    private function doesCalledMethodExistInParent(StaticCall $staticCall, Class_ $class) : bool
    {
        if (!$class->extends instanceof Name) {
            return \false;
        }
        $calledMethodName = $this->getName($staticCall->name);
        if (!\is_string($calledMethodName)) {
            return \false;
        }
        return $this->classMethodManipulator->hasParentMethodOrInterfaceMethod($class, $calledMethodName);
    }
    private function refactorExpression(Expression $expression, Class_ $class) : void
    {
        if (!$expression->expr instanceof StaticCall) {
            return;
        }
        if (!$this->isParentStaticCall($expression->expr)) {
            return;
        }
        // is valid call
        if ($this->doesCalledMethodExistInParent($expression->expr, $class)) {
            return;
        }
        $this->removeNode($expression);
    }
}
