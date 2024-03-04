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
use Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\RemoveUnusedPrivateMethodRectorTest
 */
final class RemoveUnusedPrivateMethodRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer
     */
    private $isClassMethodUsedAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer, ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused private method', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $classMethods = $node->getMethods();
        if ($classMethods === []) {
            return null;
        }
        $filter = static function (ClassMethod $classMethod) : bool {
            return $classMethod->isPrivate();
        };
        if (\array_filter($classMethods, $filter) === []) {
            return null;
        }
        if ($this->hasDynamicMethodCallOnFetchThis($classMethods)) {
            return null;
        }
        $hasChanged = \false;
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof ClassMethod) {
                continue;
            }
            if ($this->shouldSkip($stmt, $classReflection)) {
                continue;
            }
            if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node, $stmt, $scope)) {
                continue;
            }
            unset($node->stmts[$key]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkip(ClassMethod $classMethod, ?ClassReflection $classReflection) : bool
    {
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        // unreliable to detect trait, interface, anonymous class: doesn't make sense
        if ($classReflection->isTrait()) {
            return \true;
        }
        if ($classReflection->isInterface()) {
            return \true;
        }
        if ($classReflection->isAnonymous()) {
            return \true;
        }
        if (!$classMethod->isPrivate()) {
            return \true;
        }
        // skip magic methods - @see https://www.php.net/manual/en/language.oop5.magic.php
        if ($classMethod->isMagic()) {
            return \true;
        }
        return $classReflection->hasMethod(MethodName::CALL);
    }
    /**
     * @param ClassMethod[] $classMethods
     */
    private function hasDynamicMethodCallOnFetchThis(array $classMethods) : bool
    {
        foreach ($classMethods as $classMethod) {
            $isFound = (bool) $this->betterNodeFinder->findFirst((array) $classMethod->getStmts(), function (Node $subNode) : bool {
                if (!$subNode instanceof MethodCall) {
                    return \false;
                }
                if (!$subNode->var instanceof Variable) {
                    return \false;
                }
                if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                    return \false;
                }
                return $subNode->name instanceof Variable;
            });
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
}
