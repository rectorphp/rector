<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\CallableType;
use PHPStan\Type\ObjectType;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\PHPStan\ScopeFetcher;
use Rector\Reflection\ReflectionResolver;
/**
 * Decorates call args using the resolved function-like reflection, resolved once per call:
 *  - Closure/arrow-fn args passed to a variadic callable parameter (HAS_CLOSURE_WITH_VARIADIC_ARGS)
 *  - array args passed to a parameter that cannot hold a Closure, so an array callable is kept as is
 *    (IS_ARG_NOT_ACCEPTING_CLOSURE), see https://github.com/rectorphp/rector/issues/9563
 */
final class CallLikeReflectionNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof CallLike) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if ($args === []) {
            return null;
        }
        $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        $this->decorateClosureWithVariadicArgs($args, $functionLikeReflection);
        $this->decorateArgNotAcceptingClosure($node, $args, $functionLikeReflection);
        return null;
    }
    /**
     * @param Arg[] $args
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection|null $functionLikeReflection
     */
    private function decorateClosureWithVariadicArgs(array $args, $functionLikeReflection): void
    {
        foreach ($args as $arg) {
            if (!$arg->value instanceof Closure && !$arg->value instanceof ArrowFunction) {
                continue;
            }
            if ($functionLikeReflection instanceof NativeFunctionReflection) {
                $parametersAcceptors = ParametersAcceptorSelector::combineAcceptors($functionLikeReflection->getVariants());
                foreach ($parametersAcceptors->getParameters() as $extendedParameterReflection) {
                    if ($extendedParameterReflection->getType() instanceof CallableType && $extendedParameterReflection->getType()->isVariadic()) {
                        $arg->value->setAttribute(AttributeKey::HAS_CLOSURE_WITH_VARIADIC_ARGS, \true);
                    }
                }
                return;
            }
            $arg->value->setAttribute(AttributeKey::HAS_CLOSURE_WITH_VARIADIC_ARGS, \true);
        }
    }
    /**
     * @param Arg[] $args
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection|null $functionLikeReflection
     */
    private function decorateArgNotAcceptingClosure(CallLike $callLike, array $args, $functionLikeReflection): void
    {
        if (!$functionLikeReflection instanceof FunctionReflection && !$functionLikeReflection instanceof MethodReflection) {
            return;
        }
        if (!array_any($args, static fn(Arg $arg): bool => $arg->value instanceof Array_)) {
            return;
        }
        $parameterReflections = ParametersAcceptorSelectorVariantsWrapper::select($functionLikeReflection, $callLike, ScopeFetcher::fetch($callLike))->getParameters();
        $closureObjectType = new ObjectType('Closure');
        foreach ($args as $position => $arg) {
            if (!$arg->value instanceof Array_) {
                continue;
            }
            $parameterReflection = $this->matchParameterReflection($arg, $position, $parameterReflections);
            if (!$parameterReflection instanceof ParameterReflection) {
                continue;
            }
            if (!$parameterReflection->getType()->accepts($closureObjectType, \true)->no()) {
                continue;
            }
            $arg->value->setAttribute(AttributeKey::IS_ARG_NOT_ACCEPTING_CLOSURE, \true);
        }
    }
    /**
     * @param ParameterReflection[] $parameterReflections
     */
    private function matchParameterReflection(Arg $arg, int $position, array $parameterReflections): ?ParameterReflection
    {
        if ($arg->name instanceof Identifier) {
            $argName = $arg->name->toString();
            foreach ($parameterReflections as $parameterReflection) {
                if ($parameterReflection->getName() === $argName) {
                    return $parameterReflection;
                }
            }
            return null;
        }
        if (isset($parameterReflections[$position])) {
            return $parameterReflections[$position];
        }
        $lastParameterReflection = end($parameterReflections);
        if ($lastParameterReflection instanceof ParameterReflection && $lastParameterReflection->isVariadic()) {
            return $lastParameterReflection;
        }
        return null;
    }
}
