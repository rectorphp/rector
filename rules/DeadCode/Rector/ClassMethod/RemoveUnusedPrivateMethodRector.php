<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\RemoveUnusedPrivateMethodRectorTest
 */
final class RemoveUnusedPrivateMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer
     */
    private $isClassMethodUsedAnalyzer;
    public function __construct(\Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer)
    {
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused private method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node)) {
            return null;
        }
        if ($this->nodesToAddCollector->isActive()) {
            return null;
        }
        if ($this->hasDynamicMethodCallOnFetchThis($node)) {
            return null;
        }
        $this->removeNode($node);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \true;
        }
        // unreliable to detect trait, interface doesn't make sense
        if ($classReflection->isTrait()) {
            return \true;
        }
        if ($classReflection->isInterface()) {
            return \true;
        }
        if ($classReflection->isAnonymous()) {
            return \true;
        }
        // skips interfaces by default too
        if (!$classMethod->isPrivate()) {
            return \true;
        }
        // skip magic methods - @see https://www.php.net/manual/en/language.oop5.magic.php
        if ($classMethod->isMagic()) {
            return \true;
        }
        return $classReflection->hasMethod(\Rector\Core\ValueObject\MethodName::CALL);
    }
    private function hasDynamicMethodCallOnFetchThis(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $class = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        foreach ($class->getMethods() as $method) {
            $isFound = (bool) $this->betterNodeFinder->findFirst((array) $method->getStmts(), function (\PhpParser\Node $subNode) : bool {
                if (!$subNode instanceof \PhpParser\Node\Expr\MethodCall) {
                    return \false;
                }
                if (!$subNode->var instanceof \PhpParser\Node\Expr\Variable) {
                    return \false;
                }
                if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                    return \false;
                }
                return $subNode->name instanceof \PhpParser\Node\Expr\Variable;
            });
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
}
