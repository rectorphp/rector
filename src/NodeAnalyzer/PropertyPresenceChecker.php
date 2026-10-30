<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\PostRector\ValueObject\PropertyMetadata;
/**
 * Can be local property, parent property etc.
 */
final class PropertyPresenceChecker
{
    /**
     * @readonly
     */
    private PromotedPropertyResolver $promotedPropertyResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(PromotedPropertyResolver $promotedPropertyResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Includes parent classes and traits
     * @return \PhpParser\Node\Param|\PhpParser\Node\Stmt\Property|null
     */
    public function getClassContextProperty(Class_ $class, PropertyMetadata $definedPropertyWithType)
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return null;
        }
        $property = $class->getProperty($definedPropertyWithType->getName());
        if ($property instanceof Property) {
            return $property;
        }
        $promotedPropertyParams = $this->promotedPropertyResolver->resolveFromClass($class);
        foreach ($promotedPropertyParams as $promotedPropertyParam) {
            if ($this->nodeNameResolver->isName($promotedPropertyParam, $definedPropertyWithType->getName())) {
                return $promotedPropertyParam;
            }
        }
        return null;
    }
}
