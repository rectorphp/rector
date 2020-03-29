<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeFinder;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Property;
use Rector\NodeCollector\NodeCollector\ParsedPropertyFetchNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyFetchParsedNodesFinder
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ParsedPropertyFetchNodeCollector
     */
    private $parsedPropertyFetchNodeCollector;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ParsedPropertyFetchNodeCollector $parsedPropertyFetchNodeCollector
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parsedPropertyFetchNodeCollector = $parsedPropertyFetchNodeCollector;
    }

    /**
     * @return PropertyFetch[]
     */
    public function findPropertyFetchesByProperty(Property $property): array
    {
        /** @var string|null $className */
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return [];
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);

        return $this->parsedPropertyFetchNodeCollector->findPropertyFetchesByTypeAndName($className, $propertyName);
    }
}
