<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeCallerTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\NodeTypeResolver\Contract\PerNodeCallerTypeResolver\PerNodeCallerTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * This will tell the type of Node, which is calling this method
 *
 * E.g.:
 * - {$this}->callMe()
 * - $this->{getThis()}->callMe()
 * - {new John}->callMe()
 */
final class MethodCallCallerTypeResolver implements PerNodeCallerTypeResolverInterface
{
    /**
     * @var MethodReflector
     */
    private $methodReflector;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(MethodReflector $methodReflector, NodeTypeResolver $nodeTypeResolver)
    {
        $this->methodReflector = $methodReflector;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [MethodCall::class];
    }

    /**
     * Returns on magic method calls, void type, scalar types and array types.
     *
     * @param MethodCall $methodCallNode
     * @return string[]
     */
    public function resolve(Node $methodCallNode): array
    {
        if ($methodCallNode->var instanceof MethodCall) {
            $parentReturnTypes = $this->resolve($methodCallNode->var);

            /** @var Identifier $identifierNode */
            $identifierNode = $methodCallNode->var->name;

            $methodName = $identifierNode->toString();

            $returnTypes = $this->methodReflector->resolveReturnTypesForTypesAndMethod($parentReturnTypes, $methodName);

            if ($returnTypes) {
                return $returnTypes;
            }
        }

        if ($methodCallNode->var instanceof Variable || $methodCallNode->var instanceof PropertyFetch) {
            return $this->nodeTypeResolver->resolve($methodCallNode->var);
        }

        // unable to determine
        return [];
    }
}
