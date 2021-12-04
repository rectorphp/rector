<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Type\UnionTypeMethodReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\VariadicAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.reddit.com/r/PHP/comments/a1ie7g/is_there_a_linter_for_argumentcounterror_for_php/
 * @changelog http://php.net/manual/en/class.argumentcounterror.php
 *
 * @see \Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\RemoveExtraParametersRectorTest
 */
final class RemoveExtraParametersRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\VariadicAnalyzer
     */
    private $variadicAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\NodeAnalyzer\VariadicAnalyzer $variadicAnalyzer, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->variadicAnalyzer = $variadicAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NO_EXTRA_PARAMETERS;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove extra parameters', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('strlen("asdf", 1);', 'strlen("asdf");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        // unreliable count of arguments
        $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if ($functionLikeReflection instanceof \PHPStan\Reflection\Type\UnionTypeMethodReflection) {
            return null;
        }
        if ($functionLikeReflection === null) {
            return null;
        }
        if ($functionLikeReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
            $classReflection = $functionLikeReflection->getDeclaringClass();
            if ($classReflection->isInterface()) {
                return null;
            }
        }
        $maximumAllowedParameterCount = $this->resolveMaximumAllowedParameterCount($functionLikeReflection);
        $numberOfArguments = \count($node->getRawArgs());
        if ($numberOfArguments <= $maximumAllowedParameterCount) {
            return null;
        }
        for ($i = $maximumAllowedParameterCount; $i <= $numberOfArguments; ++$i) {
            unset($node->args[$i]);
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function shouldSkip($call) : bool
    {
        if ($call->args === []) {
            return \true;
        }
        if ($call instanceof \PhpParser\Node\Expr\StaticCall) {
            if (!$call->class instanceof \PhpParser\Node\Name) {
                return \true;
            }
            if ($this->isName($call->class, \Rector\Core\Enum\ObjectReference::PARENT()->getValue())) {
                return \true;
            }
        }
        return $this->variadicAnalyzer->hasVariadicParameters($call);
    }
    /**
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $functionLikeReflection
     */
    private function resolveMaximumAllowedParameterCount($functionLikeReflection) : int
    {
        $parameterCounts = [0];
        foreach ($functionLikeReflection->getVariants() as $parametersAcceptor) {
            $parameterCounts[] = \count($parametersAcceptor->getParameters());
        }
        return (int) \max($parameterCounts);
    }
}
