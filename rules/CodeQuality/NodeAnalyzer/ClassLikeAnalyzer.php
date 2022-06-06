<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class ClassLikeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string[]
     */
    public function resolvePropertyNames(Class_ $class) : array
    {
        $propertyNames = [];
        foreach ($class->getProperties() as $property) {
            $propertyNames[] = $this->nodeNameResolver->getName($property);
        }
        return $propertyNames;
    }
}
