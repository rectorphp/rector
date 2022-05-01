<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\ParamTypeByMethodCallTypeRectorTest
 */
final class ParamTypeByMethodCallTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher
     */
    private $callerParamMatcher;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(\Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher $callerParamMatcher, \RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->callerParamMatcher = $callerParamMatcher;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change param type based on passed method call type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }
        /** @var array<StaticCall|MethodCall|FuncCall> $callers */
        $callers = $this->betterNodeFinder->findInstancesOf((array) $node->stmts, [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\FuncCall::class]);
        $hasChanged = \false;
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
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
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($classMethod->params === []) {
            return \true;
        }
        if ($this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod)) {
            return \true;
        }
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \true;
        }
        return !$classReflection->isClass();
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\ComplexType $paramType
     */
    private function mirrorParamType(\PhpParser\Node\Param $decoratedParam, $paramType) : void
    {
        // mimic type
        $newParamType = $paramType;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($newParamType, function (\PhpParser\Node $node) {
            // original attributes have to removed to avoid tokens crashing from origin positions
            $node->setAttributes([]);
            return null;
        });
        $decoratedParam->type = $newParamType;
    }
    /**
     * Should skip param because one of them is conditional types?
     */
    private function isParamConditioned(\PhpParser\Node\Param $param, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            return \false;
        }
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Expr\Variable::class);
        foreach ($variables as $variable) {
            if (!$this->isName($variable, $paramName)) {
                continue;
            }
            $conditional = $this->betterNodeFinder->findParentType($variable, \PhpParser\Node\Stmt\If_::class);
            if ($conditional instanceof \PhpParser\Node\Stmt\If_) {
                return \true;
            }
            $conditional = $this->betterNodeFinder->findParentType($variable, \PhpParser\Node\Expr\Ternary::class);
            if ($conditional instanceof \PhpParser\Node\Expr\Ternary) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipParam(\PhpParser\Node\Param $param, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($this->isParamConditioned($param, $classMethod)) {
            return \true;
        }
        if ($param->variadic) {
            return \true;
        }
        // already has type, skip
        return $param->type !== null;
    }
}
