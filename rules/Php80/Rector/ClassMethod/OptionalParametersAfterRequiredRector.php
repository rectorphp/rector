<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\CodingStyle\Reflection\VendorLocationDetector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeResolver\ArgumentSorter;
use Rector\Php80\NodeResolver\RequireOptionalParamResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.0#deprecate-required-param-after-optional
 *
 * @see \Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const ALREADY_SORTED = 'already_sorted';
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
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Reflection\VendorLocationDetector
     */
    private $vendorLocationDetector;
    public function __construct(\Rector\Php80\NodeResolver\RequireOptionalParamResolver $requireOptionalParamResolver, \Rector\Php80\NodeResolver\ArgumentSorter $argumentSorter, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\CodingStyle\Reflection\VendorLocationDetector $vendorLocationDetector)
    {
        $this->requireOptionalParamResolver = $requireOptionalParamResolver;
        $this->argumentSorter = $argumentSorter;
        $this->reflectionResolver = $reflectionResolver;
        $this->vendorLocationDetector = $vendorLocationDetector;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::DEPRECATE_REQUIRED_PARAMETER_AFTER_OPTIONAL;
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
        $isAlreadySorted = (bool) $node->getAttribute(self::ALREADY_SORTED, \false);
        if ($isAlreadySorted) {
            return null;
        }
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
        $classMethodReflection = $this->reflectionResolver->resolveMethodReflectionFromClassMethod($classMethod);
        if (!$classMethodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent($classMethodReflection);
        if ($expectedArgOrParamOrder === null) {
            return null;
        }
        $newParams = $this->argumentSorter->sortArgsByExpectedParamOrder($classMethod->params, $expectedArgOrParamOrder);
        $classMethod->params = $newParams;
        $classMethod->setAttribute(self::ALREADY_SORTED, \true);
        return $classMethod;
    }
    private function refactorNew(\PhpParser\Node\Expr\New_ $new) : ?\PhpParser\Node\Expr\New_
    {
        if ($new->args === []) {
            return null;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($new);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent($methodReflection);
        if ($expectedArgOrParamOrder === null) {
            return null;
        }
        $new->args = $this->argumentSorter->sortArgsByExpectedParamOrder($new->args, $expectedArgOrParamOrder);
        $new->setAttribute(self::ALREADY_SORTED, \true);
        return $new;
    }
    private function refactorMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($methodCall);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent($methodReflection);
        if ($expectedArgOrParamOrder === null) {
            return null;
        }
        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder($methodCall->args, $expectedArgOrParamOrder);
        if ($methodCall->args === $newArgs) {
            return null;
        }
        $methodCall->args = $newArgs;
        $methodCall->setAttribute(self::ALREADY_SORTED, \true);
        return $methodCall;
    }
    /**
     * @return int[]|null
     */
    private function resolveExpectedArgParamOrderIfDifferent(\PHPStan\Reflection\MethodReflection $methodReflection) : ?array
    {
        if ($this->vendorLocationDetector->detectMethodReflection($methodReflection)) {
            return null;
        }
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        $expectedParameterReflections = $this->requireOptionalParamResolver->resolveFromReflection($methodReflection);
        if ($expectedParameterReflections === $parametersAcceptor->getParameters()) {
            return null;
        }
        return \array_keys($expectedParameterReflections);
    }
}
