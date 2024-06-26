<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use Rector\CodingStyle\Reflection\VendorLocationDetector;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\Php80\NodeResolver\ArgumentSorter;
use Rector\Php80\NodeResolver\RequireOptionalParamResolver;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeResolver\RequireOptionalParamResolver
     */
    private $requireOptionalParamResolver;
    /**
     * @readonly
     * @var \Rector\Php80\NodeResolver\ArgumentSorter
     */
    private $argumentSorter;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Reflection\VendorLocationDetector
     */
    private $vendorLocationDetector;
    /**
     * @var string
     */
    private const HAS_SWAPPED_PARAMS = 'has_swapped_params';
    public function __construct(RequireOptionalParamResolver $requireOptionalParamResolver, ArgumentSorter $argumentSorter, ReflectionResolver $reflectionResolver, VendorLocationDetector $vendorLocationDetector)
    {
        $this->requireOptionalParamResolver = $requireOptionalParamResolver;
        $this->argumentSorter = $argumentSorter;
        $this->reflectionResolver = $reflectionResolver;
        $this->vendorLocationDetector = $vendorLocationDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move required parameters after optional ones', [new CodeSample(<<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, $required)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($required, $optional = 1)
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
        return [ClassMethod::class, Function_::class, New_::class, MethodCall::class, StaticCall::class, FuncCall::class];
    }
    /**
     * @param ClassMethod|Function_|New_|MethodCall|StaticCall|FuncCall $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|null|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall
     */
    public function refactorWithScope(Node $node, Scope $scope)
    {
        if ($node instanceof ClassMethod || $node instanceof Function_) {
            return $this->refactorClassMethodOrFunction($node, $scope);
        }
        if ($node instanceof New_) {
            return $this->refactorNew($node, $scope);
        }
        return $this->refactorMethodCallOrFuncCall($node, $scope);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|null
     */
    private function refactorClassMethodOrFunction($node, Scope $scope)
    {
        if ($node->params === []) {
            return null;
        }
        if ($node->getAttribute(self::HAS_SWAPPED_PARAMS, \false) === \true) {
            return null;
        }
        if ($node instanceof ClassMethod) {
            $reflection = $this->reflectionResolver->resolveMethodReflectionFromClassMethod($node, $scope);
        } else {
            $reflection = $this->reflectionResolver->resolveFunctionReflectionFromFunction($node, $scope);
        }
        if (!$reflection instanceof MethodReflection && !$reflection instanceof FunctionReflection) {
            return null;
        }
        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent($reflection, $node, $scope);
        if ($expectedArgOrParamOrder === null) {
            return null;
        }
        $node->params = $this->argumentSorter->sortArgsByExpectedParamOrder($node->params, $expectedArgOrParamOrder);
        $node->setAttribute(self::HAS_SWAPPED_PARAMS, \true);
        return $node;
    }
    private function refactorNew(New_ $new, Scope $scope) : ?New_
    {
        if ($new->args === []) {
            return null;
        }
        if ($new->isFirstClassCallable()) {
            return null;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($new);
        if (!$methodReflection instanceof MethodReflection) {
            return null;
        }
        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent($methodReflection, $new, $scope);
        if ($expectedArgOrParamOrder === null) {
            return null;
        }
        $new->args = $this->argumentSorter->sortArgsByExpectedParamOrder($new->getArgs(), $expectedArgOrParamOrder);
        return $new;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall|null
     */
    private function refactorMethodCallOrFuncCall($node, Scope $scope)
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$reflection instanceof MethodReflection && !$reflection instanceof FunctionReflection) {
            return null;
        }
        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent($reflection, $node, $scope);
        if ($expectedArgOrParamOrder === null) {
            return null;
        }
        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder($node->getArgs(), $expectedArgOrParamOrder);
        if ($node->args === $newArgs) {
            return null;
        }
        $node->args = $newArgs;
        return $node;
    }
    /**
     * @return int[]|null
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $reflection
     * @param \PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $node
     */
    private function resolveExpectedArgParamOrderIfDifferent($reflection, $node, Scope $scope) : ?array
    {
        if ($reflection instanceof NativeFunctionReflection) {
            return null;
        }
        if ($reflection instanceof MethodReflection && $this->vendorLocationDetector->detectMethodReflection($reflection)) {
            return null;
        }
        if ($reflection instanceof FunctionReflection && $this->vendorLocationDetector->detectFunctionReflection($reflection)) {
            return null;
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($reflection, $node, $scope);
        $expectedParameterReflections = $this->requireOptionalParamResolver->resolveFromParametersAcceptor($parametersAcceptor);
        if ($expectedParameterReflections === $parametersAcceptor->getParameters()) {
            return null;
        }
        return \array_keys($expectedParameterReflections);
    }
}
