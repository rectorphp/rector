<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\RemoveParentCallWithoutParentRectorTest
 */
final class RemoveParentCallWithoutParentRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassMethodManipulator
     */
    private $classMethodManipulator;
    /**
     * @readonly
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
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
    public function __construct(ClassMethodManipulator $classMethodManipulator, ParentClassScopeResolver $parentClassScopeResolver, ClassAnalyzer $classAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
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
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$classLike instanceof Class_) {
            return null;
        }
        if ($this->shouldSkip($node, $classLike)) {
            return null;
        }
        $parentClassReflection = $this->parentClassScopeResolver->resolveParentClassReflection($scope);
        if (!$parentClassReflection instanceof ClassReflection) {
            return $this->processNoParentReflection($node);
        }
        $classMethod = $this->betterNodeFinder->findParentType($node, ClassMethod::class);
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        if ($this->classAnalyzer->isAnonymousClass($classLike)) {
            // currently the classMethodManipulator isn't able to find usages of anonymous classes
            return null;
        }
        $calledMethodName = $this->getName($node->name);
        if ($this->classMethodManipulator->hasParentMethodOrInterfaceMethod($classMethod, $calledMethodName)) {
            return null;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Expression) {
            return null;
        }
        $this->removeNode($node);
        return null;
    }
    private function shouldSkip(StaticCall $staticCall, Class_ $class) : bool
    {
        if (!$staticCall->class instanceof Name) {
            return \true;
        }
        if (!$this->isName($staticCall->class, ObjectReference::PARENT)) {
            return \true;
        }
        return $class->extends instanceof FullyQualified && !$this->reflectionProvider->hasClass($class->extends->toString());
    }
    private function processNoParentReflection(StaticCall $staticCall) : ?ConstFetch
    {
        $parentNode = $staticCall->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Expression) {
            return $this->nodeFactory->createNull();
        }
        $this->removeNode($staticCall);
        return null;
    }
}
