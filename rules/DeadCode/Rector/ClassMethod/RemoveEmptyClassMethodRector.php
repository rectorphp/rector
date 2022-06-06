<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\NodeManipulator\ControllerClassMethodManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\RemoveEmptyClassMethodRectorTest
 */
final class RemoveEmptyClassMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassMethodManipulator
     */
    private $classMethodManipulator;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\ControllerClassMethodManipulator
     */
    private $controllerClassMethodManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\ClassMethodManipulator $classMethodManipulator, \Rector\DeadCode\NodeManipulator\ControllerClassMethodManipulator $controllerClassMethodManipulator, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->controllerClassMethodManipulator = $controllerClassMethodManipulator;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove empty class methods not required by parents', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class OrphanClass
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        if ($node->stmts !== null && $node->stmts !== []) {
            return null;
        }
        if ($node->isAbstract()) {
            return null;
        }
        if ($node->isFinal() && !$classLike->isFinal()) {
            return null;
        }
        if ($this->shouldSkipNonFinalNonPrivateClassMethod($classLike, $node)) {
            return null;
        }
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }
        $this->removeNode($node);
        return $node;
    }
    private function shouldSkipNonFinalNonPrivateClassMethod(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($class->isFinal()) {
            return \false;
        }
        if ($classMethod->isMagic()) {
            return \false;
        }
        if ($classMethod->isProtected()) {
            return \true;
        }
        return $classMethod->isPublic();
    }
    private function shouldSkipClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($this->classMethodManipulator->isNamedConstructor($classMethod)) {
            return \true;
        }
        if ($this->classMethodManipulator->hasParentMethodOrInterfaceMethod($classMethod)) {
            return \true;
        }
        if ($this->paramAnalyzer->hasPropertyPromotion($classMethod->params)) {
            return \true;
        }
        if ($this->controllerClassMethodManipulator->isControllerClassMethodWithBehaviorAnnotation($classMethod)) {
            return \true;
        }
        if ($this->nodeNameResolver->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            $class = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\Class_::class);
            return $class instanceof \PhpParser\Node\Stmt\Class_ && $class->extends instanceof \PhpParser\Node\Name\FullyQualified;
        }
        return $this->nodeNameResolver->isName($classMethod, \Rector\Core\ValueObject\MethodName::INVOKE);
    }
}
