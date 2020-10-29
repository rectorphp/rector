<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassLikeAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return string[]
     */
    public function resolvePropertyNames(ClassLike $classLike): array
    {
        $propertyNames = [];

        foreach ($classLike->getProperties() as $property) {
            $propertyNames[] = $this->nodeNameResolver->getName($property);
        }

        return $propertyNames;
    }
}
