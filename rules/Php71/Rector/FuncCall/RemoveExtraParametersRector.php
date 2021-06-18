<?php

declare(strict_types=1);

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
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;
use Rector\Core\PHPStan\Reflection\VariadicAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://www.reddit.com/r/PHP/comments/a1ie7g/is_there_a_linter_for_argumentcounterror_for_php/
 * @changelog http://php.net/manual/en/class.argumentcounterror.php
 *
 * @see \Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\RemoveExtraParametersRectorTest
 */
final class RemoveExtraParametersRector extends AbstractRector
{
    public function __construct(
        private CallReflectionResolver $callReflectionResolver,
        private VariadicAnalyzer $variadicAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove extra parameters', [
            new CodeSample('strlen("asdf", 1);', 'strlen("asdf");'),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        // unreliable count of arguments
        $functionLikeReflection = $this->callReflectionResolver->resolveCall($node);
        if ($functionLikeReflection instanceof UnionTypeMethodReflection) {
            return null;
        }

        if ($functionLikeReflection === null) {
            return null;
        }

        if ($functionLikeReflection instanceof PhpMethodReflection) {
            $classReflection = $functionLikeReflection->getDeclaringClass();
            if ($classReflection->isInterface()) {
                return null;
            }
        }

        $maximumAllowedParameterCount = $this->resolveMaximumAllowedParameterCount($functionLikeReflection);

        $numberOfArguments = count($node->args);
        if ($numberOfArguments <= $maximumAllowedParameterCount) {
            return null;
        }

        for ($i = $maximumAllowedParameterCount; $i <= $numberOfArguments; ++$i) {
            unset($node->args[$i]);
        }

        return $node;
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    private function shouldSkip(Node $node): bool
    {
        if ($node->args === []) {
            return true;
        }

        if ($node instanceof StaticCall) {
            if (! $node->class instanceof Name) {
                return true;
            }

            if ($this->isName($node->class, 'parent')) {
                return true;
            }
        }

        $functionReflection = $this->callReflectionResolver->resolveCall($node);
        if ($functionReflection === null) {
            return true;
        }

        if ($functionReflection->getVariants() === []) {
            return true;
        }

        return $this->variadicAnalyzer->hasVariadicParameters($functionReflection);
    }

    /**
     * @param MethodReflection|FunctionReflection $reflection
     */
    private function resolveMaximumAllowedParameterCount(object $reflection): int
    {
        $parameterCounts = [0];
        foreach ($reflection->getVariants() as $parametersAcceptor) {
            $parameterCounts[] = count($parametersAcceptor->getParameters());
        }

        return (int) max($parameterCounts);
    }
}
