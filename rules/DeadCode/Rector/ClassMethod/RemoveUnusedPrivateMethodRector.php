<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\RemoveUnusedPrivateMethodRectorTest
 */
final class RemoveUnusedPrivateMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private DataProviderMethodsFinder $dataProviderMethodsFinder;
    public function __construct(IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer, ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder, DataProviderMethodsFinder $dataProviderMethodsFinder)
    {
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getMethods() === []) {
            return null;
        }
        $hasChanged = \false;
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $dataProviderMethodNames = $this->resolveDataProviderMethodNames($node);
        foreach ($node->stmts as $classStmtKey => $classStmt) {
            if (!$classStmt instanceof ClassMethod) {
                continue;
            }
            if (!$classStmt->isPrivate()) {
                continue;
            }
            $classMethod = $classStmt;
            if ($this->hasDynamicMethodCallOnFetchThis($classStmt)) {
                continue;
            }
            $scope = ScopeFetcher::fetch($node);
            if ($this->shouldSkip($classStmt, $classReflection)) {
                continue;
            }
            if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node, $classStmt, $scope)) {
                continue;
            }
            if ($this->isNames($classMethod, $dataProviderMethodNames)) {
                continue;
            }
            unset($node->stmts[$classStmtKey]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkip(ClassMethod $classMethod, ClassReflection $classReflection): bool
    {
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
        // skip magic methods - @see https://www.php.net/manual/en/language.oop5.magic.php
        if ($classMethod->isMagic()) {
            return \true;
        }
        return $classReflection->hasMethod(MethodName::CALL);
    }
    private function hasDynamicMethodCallOnFetchThis(ClassMethod $classMethod): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $subNode): bool {
            if (!$subNode instanceof MethodCall) {
                return \false;
            }
            if (!$subNode->var instanceof Variable) {
                return \false;
            }
            if (!$this->isName($subNode->var, 'this')) {
                return \false;
            }
            return $subNode->name instanceof Variable;
        });
    }
    /**
     * @return string[]
     */
    private function resolveDataProviderMethodNames(Class_ $class): array
    {
        $dataProviderClassMethods = $this->dataProviderMethodsFinder->findDataProviderNodesInClass($class);
        return $this->nodeNameResolver->getNames($dataProviderClassMethods);
    }
}
