<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FuncCallNameResolver implements NodeNameResolverInterface
{
    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNode(): string
    {
        return FuncCall::class;
    }

    /**
     * If some function is namespaced, it will be used over global one.
     * But only if it really exists.
     *
     * @param FuncCall $node
     */
    public function resolve(Node $node): ?string
    {
        if ($node->name instanceof Expr) {
            return null;
        }

        $functionName = $node->name;

        $namespaceName = $functionName->getAttribute(AttributeKey::NAMESPACED_NAME);
        if ($namespaceName instanceof FullyQualified) {
            $functionFqnName = $namespaceName->toString();

            if ($this->reflectionProvider->hasFunction($namespaceName, null)) {
                return $functionFqnName;
            }
        }

        return (string) $functionName;
    }
}
