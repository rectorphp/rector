<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\NodeNameResolver;
final class AttributeFinder
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
     * @param class-string $attributeClass
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $node
     */
    public function findAttributeByClassArgByName($node, string $attributeClass, string $argName) : ?\PhpParser\Node\Expr
    {
        return $this->findAttributeByClassesArgByName($node, [$attributeClass], $argName);
    }
    /**
     * @param class-string[] $attributeClasses
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $node
     */
    public function findAttributeByClassesArgByName($node, array $attributeClasses, string $argName) : ?\PhpParser\Node\Expr
    {
        $attribute = $this->findAttributeByClasses($node, $attributeClasses);
        if (!$attribute instanceof \PhpParser\Node\Attribute) {
            return null;
        }
        return $this->findArgByName($attribute, $argName);
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    public function findArgByName(\PhpParser\Node\Attribute $attribute, string $argName)
    {
        foreach ($attribute->args as $arg) {
            if ($arg->name === null) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->name, $argName)) {
                continue;
            }
            return $arg->value;
        }
        return null;
    }
    /**
     * @param class-string $attributeClass
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $node
     */
    public function findAttributeByClass($node, string $attributeClass) : ?\PhpParser\Node\Attribute
    {
        /** @var AttributeGroup $attrGroup */
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$attribute->name instanceof \PhpParser\Node\Name\FullyQualified) {
                    continue;
                }
                if ($this->nodeNameResolver->isName($attribute->name, $attributeClass)) {
                    return $attribute;
                }
            }
        }
        return null;
    }
    /**
     * @param class-string[] $attributeClasses
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $node
     */
    public function findAttributeByClasses($node, array $attributeClasses) : ?\PhpParser\Node\Attribute
    {
        foreach ($attributeClasses as $attributeClass) {
            $attribute = $this->findAttributeByClass($node, $attributeClass);
            if ($attribute instanceof \PhpParser\Node\Attribute) {
                return $attribute;
            }
        }
        return null;
    }
}
