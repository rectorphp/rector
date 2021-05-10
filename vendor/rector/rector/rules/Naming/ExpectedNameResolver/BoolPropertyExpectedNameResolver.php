<?php

declare (strict_types=1);
namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class BoolPropertyExpectedNameResolver
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->propertyNaming = $propertyNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolve(\PhpParser\Node\Stmt\Property $property) : ?string
    {
        if (!$this->nodeTypeResolver->isPropertyBoolean($property)) {
            return null;
        }
        $expectedName = $this->propertyNaming->getExpectedNameFromBooleanPropertyType($property);
        if ($expectedName === null) {
            return null;
        }
        // skip if already has suffix
        $currentName = $this->nodeNameResolver->getName($property);
        if ($this->nodeNameResolver->endsWith($currentName, $expectedName)) {
            return null;
        }
        return $expectedName;
    }
}
