<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Rector\NodeNameResolver\NodeNameResolver;

final class PropertyFetchAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isLocalPropertyFetch(Node $node): bool
    {
        if ($node instanceof PropertyFetch) {
            if ($node->var instanceof MethodCall) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->var, 'this');
        }

        if ($node instanceof StaticPropertyFetch) {
            return $this->nodeNameResolver->isName($node->class, 'self');
        }

        return false;
    }

    public function isLocalPropertyFetchName(Node $node, string $desiredPropertyName): bool
    {
        if (! $this->isLocalPropertyFetch($node)) {
            return false;
        }

        /** @var PropertyFetch|StaticPropertyFetch $node */
        return $this->nodeNameResolver->isName($node->name, $desiredPropertyName);
    }
}
