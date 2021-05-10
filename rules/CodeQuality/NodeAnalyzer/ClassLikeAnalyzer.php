<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassLikeAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @return string[]
     */
    public function resolvePropertyNames(Class_ $class): array
    {
        $propertyNames = [];

        foreach ($class->getProperties() as $property) {
            $propertyNames[] = $this->nodeNameResolver->getName($property);
        }

        return $propertyNames;
    }
}
