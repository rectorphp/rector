<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassLikeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string[]
     */
    public function resolvePropertyNames(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $propertyNames = [];
        foreach ($class->getProperties() as $property) {
            $propertyNames[] = $this->nodeNameResolver->getName($property);
        }
        return $propertyNames;
    }
}
