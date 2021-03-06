<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassConstFetchTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * To avoid cricular references
     * @required
     */
    public function autowireClassConstFetchTypeResolver(
        NodeTypeResolver $nodeTypeResolver,
        NodeNameResolver $nodeNameResolver
    ): void {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function resolve(Node $node): Type
    {
        if ($this->nodeNameResolver->isName($node->name, 'class')) {
            $className = $this->nodeNameResolver->getName($node->class);
            if ($className !== null) {
                return new GenericClassStringType(new ObjectType($className));
            }
        }

        return $this->nodeTypeResolver->resolve($node->class);
    }
}
