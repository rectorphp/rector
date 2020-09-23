<?php

declare(strict_types=1);

namespace Rector\Naming\ConflictingNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeNameResolver\NodeNameResolver;

class PropertyConflictingNameResolver extends AbstractConflictingNameResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ArrayFilter
     */
    private $arrayFilter;

    public function __construct(NodeNameResolver $nodeNameResolver, ArrayFilter $arrayFilter)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
    }

    /**
     * @param ClassLike $node
     */
    public function resolve(Node $node): array
    {
        $expectedNames = [];
        foreach ($node->getProperties() as $property) {
            $expectedName = $this->expectedNameResolver->resolve($property);
            if ($expectedName === null) {
                /** @var string $expectedName */
                $expectedName = $this->nodeNameResolver->getName($property);
            }

            $expectedNames[] = $expectedName;
        }

        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }
}
