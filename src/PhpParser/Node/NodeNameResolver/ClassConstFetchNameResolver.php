<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Core\Contract\NameResolver\NodeNameResolverInterface;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;

final class ClassConstFetchNameResolver implements NodeNameResolverInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @required
     */
    public function autowireClassNameResolver(NodeNameResolver $nodeNameResolver): void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getNode(): string
    {
        return ClassConstFetch::class;
    }

    /**
     * @param ClassConstFetch $node
     */
    public function resolve(Node $node): ?string
    {
        $class = $this->nodeNameResolver->getName($node->class);
        $name = $this->nodeNameResolver->getName($node->name);

        if ($class === null || $name === null) {
            return null;
        }

        return $class . '::' . $name;
    }
}
