<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @implements NodeNameResolverInterface<FuncCall>
 */
final class FuncCallNameResolver implements NodeNameResolverInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getNode() : string
    {
        return FuncCall::class;
    }
    /**
     * If some function is namespaced, it will be used over global one.
     * But only if it really exists.
     *
     * @param FuncCall $node
     */
    public function resolve(Node $node) : ?string
    {
        if ($node->name instanceof Expr) {
            return null;
        }
        $namespaceName = $node->name->getAttribute(AttributeKey::NAMESPACED_NAME);
        if ($namespaceName instanceof FullyQualified) {
            $functionFqnName = $namespaceName->toString();
            if ($this->reflectionProvider->hasFunction($namespaceName, null)) {
                return $functionFqnName;
            }
        }
        return (string) $node->name;
    }
}
