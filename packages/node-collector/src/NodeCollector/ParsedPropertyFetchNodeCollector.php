<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * All parsed nodes grouped type
 */
final class ParsedPropertyFetchNodeCollector
{
    /**
     * @var PropertyFetch[][][]
     */
    private $propertyFetchesByTypeAndName = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * To prevent circular reference
     * @required
     */
    public function autowireParsedNodesByType(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function collect(Node $node): void
    {
        if (! $node instanceof PropertyFetch) {
            return;
        }

        $propertyType = $this->nodeTypeResolver->getStaticType($node->var);
        $propertyName = $this->nodeNameResolver->getName($node->name);

        if ($propertyType instanceof TypeWithClassName) {
            $this->propertyFetchesByTypeAndName[$propertyType->getClassName()][$propertyName][] = $node;
        }

        if ($propertyType instanceof UnionType) {
            foreach ($propertyType->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                $this->propertyFetchesByTypeAndName[$unionedType->getClassName()][$propertyName][] = $node;
            }
        }
    }

    /**
     * @return PropertyFetch[]
     */
    public function findPropertyFetchesByTypeAndName(string $className, string $propertyName): array
    {
        return $this->propertyFetchesByTypeAndName[$className][$propertyName] ?? [];
    }
}
