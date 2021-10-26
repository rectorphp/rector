<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\NodeFinder;

final class SkipArrayTypeFromChildMethod
{
    public function __construct(
        private NodeFinder $nodeFinder
    ) {
    }

    /**
     * @return StaticPropertyFetch[]|PropertyFetch[]
     */
    public function getProperties($nodes): array
    {
        return $this->findProperties($nodes);
    }

    /**
     * @return StaticPropertyFetch[]|PropertyFetch[]
     */
    private function findProperties($nodes): array
    {
        /** @var PropertyFetch[] $properites */
        $propertyFetches = $this->nodeFinder->findInstanceOf($nodes, PropertyFetch::class);

        /** @var StaticPropertyFetch[] $properites */
        $staticPropertyFetches = $this->nodeFinder->findInstanceOf($nodes, StaticPropertyFetch::class);

        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = array_merge($propertyFetches, $staticPropertyFetches);

        return $propertyFetches;
    }
}
