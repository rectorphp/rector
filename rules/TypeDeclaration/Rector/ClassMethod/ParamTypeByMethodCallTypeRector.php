<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\ConflictGuard\ParentClassMethodTypeOverrideGuard;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\ParamTypeByMethodCallTypeRectorTest
 */
final class ParamTypeByMethodCallTypeRector extends AbstractRector
{
    public function __construct(
        private CallerParamMatcher $callerParamMatcher,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change param type based on passed method call type', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(string $name)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go($value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(string $name)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go(string $value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        /** @var array<StaticCall|MethodCall|FuncCall> $callers */
        $callers = $this->betterNodeFinder->findInstancesOf(
            (array) $node->stmts,
            [StaticCall::class, MethodCall::class, FuncCall::class]
        );

        $hasChanged = false;
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        foreach ($node->params as $param) {
            if ($this->shouldSkipParam($param, $node)) {
                continue;
            }

            foreach ($callers as $caller) {
                $paramType = $this->callerParamMatcher->matchCallParamType($caller, $param, $scope);
                if ($paramType === null) {
                    continue;
                }

                $this->mirrorParamType($param, $paramType);
                $hasChanged = true;
            }
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($classMethod->params === []) {
            return true;
        }

        if ($this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod)) {
            return true;
        }

        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return true;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        return ! $classReflection->isClass();
    }

    private function mirrorParamType(
        Param $decoratedParam,
        Identifier | Name | NullableType | UnionType $paramType
    ): void {
        // mimic type
        $newParamType = $paramType;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            $newParamType,
            function (Node $node): void {
                // original attributes have to removed to avoid tokens crashing from origin positions
                $node->setAttributes([]);
            }
        );

        $decoratedParam->type = $newParamType;
    }

    /**
     * Should skip param because one of them is conditional types?
     */
    private function isParamConditioned(Param $param, ClassMethod $classMethod): bool
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            return false;
        }

        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Variable::class);

        foreach ($variables as $variable) {
            if (! $this->isName($variable, $paramName)) {
                continue;
            }

            $conditional = $this->betterNodeFinder->findParentType($variable, If_::class);
            if ($conditional instanceof If_) {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipParam(Param $param, ClassMethod $classMethod): bool
    {
        if ($this->isParamConditioned($param, $classMethod)) {
            return true;
        }

        // already has type, skip
        return $param->type !== null;
    }
}
