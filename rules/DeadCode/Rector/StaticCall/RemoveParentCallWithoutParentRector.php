<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\RemoveParentCallWithoutParentRectorTest
 */
final class RemoveParentCallWithoutParentRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\ClassMethodManipulator
     */
    private $classMethodManipulator;
    /**
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\ClassMethodManipulator $classMethodManipulator, \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
        $this->classAnalyzer = $classAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused parent call with no parent class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        if (!$node->class instanceof \PhpParser\Node\Name) {
            return null;
        }
        if (!$this->isName($node->class, \Rector\Core\Enum\ObjectReference::PARENT()->getValue())) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $parentClassReflection = $this->parentClassScopeResolver->resolveParentClassReflection($scope);
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            $this->removeNode($node);
            return null;
        }
        $classMethod = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassMethod::class);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
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
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $this->removeNode($node);
        return null;
    }
}
