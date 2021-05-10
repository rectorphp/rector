<?php

declare (strict_types=1);
namespace Rector\RemovingStatic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\LocallyCalledStaticMethodToNonStaticRectorTest
 */
final class LocallyCalledStaticMethodToNonStaticRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ClassMethodVisibilityGuard
     */
    private $classMethodVisibilityGuard;
    public function __construct(\Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard $classMethodVisibilityGuard)
    {
        $this->classMethodVisibilityGuard = $classMethodVisibilityGuard;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change static method and local-only calls to non-static', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param ClassMethod|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        return $this->refactorStaticCall($node);
    }
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!$classMethod->isStatic()) {
            return null;
        }
        if (!$this->isClassMethodWithOnlyLocalStaticCalls($classMethod)) {
            return null;
        }
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($classMethod, $classReflection)) {
            return null;
        }
        // change static calls to non-static ones, but only if in non-static method!!!
        $this->visibilityManipulator->makeNonStatic($classMethod);
        return $classMethod;
    }
    private function refactorStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        $classMethod = $this->nodeRepository->findClassMethodByStaticCall($staticCall);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        // is static call in the same as class method
        if (!$this->haveSharedClass($classMethod, [$staticCall])) {
            return null;
        }
        if ($this->isInStaticClassMethod($staticCall)) {
            return null;
        }
        $thisVariable = new \PhpParser\Node\Expr\Variable('this');
        return new \PhpParser\Node\Expr\MethodCall($thisVariable, $staticCall->name, $staticCall->args);
    }
    private function isClassMethodWithOnlyLocalStaticCalls(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $staticCalls = $this->nodeRepository->findStaticCallsByClassMethod($classMethod);
        // get static staticCalls
        return $this->haveSharedClass($classMethod, $staticCalls);
    }
    /**
     * @param Node[] $nodes
     */
    private function haveSharedClass(\PhpParser\Node $mainNode, array $nodes) : bool
    {
        $mainNodeClass = $mainNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        foreach ($nodes as $node) {
            $nodeClass = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            if ($mainNodeClass !== $nodeClass) {
                return \false;
            }
        }
        return \true;
    }
    private function isInStaticClassMethod(\PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        $locationClassMethod = $staticCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$locationClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        return $locationClassMethod->isStatic();
    }
}
