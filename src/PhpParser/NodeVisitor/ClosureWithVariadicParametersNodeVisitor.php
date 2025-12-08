<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\CallableType;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Reflection\ReflectionResolver;
/**
 * Decorate method call, function call or static call, that accepts closure that
 * requires multiple args (variadic) - to handle them later in specific rules.
 */
final class ClosureWithVariadicParametersNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
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
        if ($node->getArgs() === []) {
            return null;
        }
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        foreach ($node->getArgs() as $arg) {
            if (!$arg->value instanceof Closure && !$arg->value instanceof ArrowFunction) {
                continue;
            }
            if ($methodReflection instanceof NativeFunctionReflection) {
                $parametersAcceptors = ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants());
                foreach ($parametersAcceptors->getParameters() as $extendedParameterReflection) {
                    if ($extendedParameterReflection->getType() instanceof CallableType && $extendedParameterReflection->getType()->isVariadic()) {
                        $arg->value->setAttribute(AttributeKey::HAS_CLOSURE_WITH_VARIADIC_ARGS, \true);
                    }
                }
                return null;
            }
            $arg->value->setAttribute(AttributeKey::HAS_CLOSURE_WITH_VARIADIC_ARGS, \true);
        }
        return null;
    }
}
