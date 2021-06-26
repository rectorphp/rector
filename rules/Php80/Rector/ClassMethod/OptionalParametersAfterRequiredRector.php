<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;
use Rector\Core\PHPStan\Reflection\ClassMethodReflectionResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Php80\NodeResolver\ArgumentSorter;
use Rector\Php80\NodeResolver\RequireOptionalParamResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.0#deprecate-required-param-after-optional
 *
 * @see \Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Php80\NodeResolver\RequireOptionalParamResolver
     */
    private $requireOptionalParamResolver;
    /**
     * @var \Rector\Php80\NodeResolver\ArgumentSorter
     */
    private $argumentSorter;
    /**
     * @var \Rector\Core\PHPStan\Reflection\CallReflectionResolver
     */
    private $callReflectionResolver;
    /**
     * @var \Rector\Core\PHPStan\Reflection\ClassMethodReflectionResolver
     */
    private $classMethodReflectionResolver;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\Php80\NodeResolver\RequireOptionalParamResolver $requireOptionalParamResolver, \Rector\Php80\NodeResolver\ArgumentSorter $argumentSorter, \Rector\Core\PHPStan\Reflection\CallReflectionResolver $callReflectionResolver, \Rector\Core\PHPStan\Reflection\ClassMethodReflectionResolver $classMethodReflectionResolver, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->requireOptionalParamResolver = $requireOptionalParamResolver;
        $this->argumentSorter = $argumentSorter;
        $this->callReflectionResolver = $callReflectionResolver;
        $this->classMethodReflectionResolver = $classMethodReflectionResolver;
        $this->astResolver = $astResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move required parameters after optional ones', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\New_::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param ClassMethod|New_|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\New_) {
            return $this->refactorNew($node);
        }
        return $this->refactorMethodCall($node);
    }
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if ($classMethod->params === []) {
            return null;
        }
        $classMethodReflection = $this->classMethodReflectionResolver->resolve($classMethod);
        if (!$classMethodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        $parametersAcceptor = $classMethodReflection->getVariants()[0];
        $expectedOrderParameterReflections = $this->requireOptionalParamResolver->resolveFromReflection($classMethodReflection);
        if ($parametersAcceptor->getParameters() === $expectedOrderParameterReflections) {
            return null;
        }
        $newParams = $this->argumentSorter->sortArgsByExpectedParamOrder($classMethod->params, $expectedOrderParameterReflections);
        $classMethod->params = $newParams;
        return $classMethod;
    }
    private function refactorNew(\PhpParser\Node\Expr\New_ $new) : ?\PhpParser\Node\Expr\New_
    {
        if ($new->args === []) {
            return null;
        }
        $newClassType = $this->nodeTypeResolver->resolve($new->class);
        if (!$newClassType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $classMethod = $this->astResolver->resolveClassMethod($newClassType->getClassName(), \Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        $classMethodReflection = $this->classMethodReflectionResolver->resolve($classMethod);
        if (!$classMethodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        $parametersAcceptor = $classMethodReflection->getVariants()[0];
        $expectedOrderedParameterReflections = $this->requireOptionalParamResolver->resolveFromReflection($classMethodReflection);
        if ($expectedOrderedParameterReflections === $parametersAcceptor->getParameters()) {
            return null;
        }
        if (\count($new->args) !== \count($classMethod->getParams())) {
            return null;
        }
        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder($new->args, $expectedOrderedParameterReflections);
        if ($new->args === $newArgs) {
            return null;
        }
        $new->args = $newArgs;
        return $new;
    }
    private function refactorMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        $callReflection = $this->callReflectionResolver->resolveCall($methodCall);
        if ($callReflection === null) {
            return null;
        }
        $parametersAcceptor = $callReflection->getVariants()[0];
        $expectedOrderedParameterReflections = $this->requireOptionalParamResolver->resolveFromReflection($callReflection);
        if ($expectedOrderedParameterReflections === $parametersAcceptor->getParameters()) {
            return null;
        }
        if (\count($methodCall->args) !== \count($parametersAcceptor->getParameters())) {
            return null;
        }
        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder($methodCall->args, $expectedOrderedParameterReflections);
        if ($methodCall->args === $newArgs) {
            return null;
        }
        $methodCall->args = $newArgs;
        return $methodCall;
    }
}
