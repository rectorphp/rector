<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\NodeNameResolver;
final class AttributeFinder
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $node
     */
    public function findAttributeByClass($node, string $desiredAttributeClass) : ?\PhpParser\Node\Attribute
    {
        /** @var AttributeGroup $attrGroup */
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$attribute->name instanceof \PhpParser\Node\Name\FullyQualified) {
                    continue;
                }
                if ($this->nodeNameResolver->isName($attribute->name, $desiredAttributeClass)) {
                    return $attribute;
                }
            }
        }
        return null;
    }
}
