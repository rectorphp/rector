<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\Enum\MappingClass;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * @api
 */
final class AttributeFinder
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param MappingClass::* $attributeClass
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function findAttributeByClassArgByName($node, string $attributeClass, string $argName) : ?Expr
    {
        return $this->findAttributeByClassesArgByName($node, [$attributeClass], $argName);
    }
    /**
     * @param string[] $attributeClasses
     * @param string[] $argNames
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function findAttributeByClassesArgByNames($node, array $attributeClasses, array $argNames) : ?Expr
    {
        $attribute = $this->findAttributeByClasses($node, $attributeClasses);
        if (!$attribute instanceof Attribute) {
            return null;
        }
        foreach ($argNames as $argName) {
            $argExpr = $this->findArgByName($attribute, $argName);
            if ($argExpr instanceof Expr) {
                return $argExpr;
            }
        }
        return null;
    }
    /**
     * @param string[] $attributeClasses
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function findAttributeByClassesArgByName($node, array $attributeClasses, string $argName) : ?Expr
    {
        $attribute = $this->findAttributeByClasses($node, $attributeClasses);
        if (!$attribute instanceof Attribute) {
            return null;
        }
        return $this->findArgByName($attribute, $argName);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function findAttributeByClass($node, string $attributeClass) : ?Attribute
    {
        /** @var AttributeGroup $attrGroup */
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$attribute->name instanceof FullyQualified) {
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
     * @return Attribute[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function findManyByClass($node, string $attributeClass) : array
    {
        $attributes = [];
        /** @var AttributeGroup $attrGroup */
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$attribute->name instanceof FullyQualified) {
                    continue;
                }
                if ($this->nodeNameResolver->isName($attribute->name, $attributeClass)) {
                    $attributes[] = $attribute;
                }
            }
        }
        return $attributes;
    }
    /**
     * @param string[] $attributeClasses
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function findAttributeByClasses($node, array $attributeClasses) : ?Attribute
    {
        foreach ($attributeClasses as $attributeClass) {
            $attribute = $this->findAttributeByClass($node, $attributeClass);
            if ($attribute instanceof Attribute) {
                return $attribute;
            }
        }
        return null;
    }
    /**
     * @param string[] $attributeClasses
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function hasAttributeByClasses($node, array $attributeClasses) : bool
    {
        return $this->findAttributeByClasses($node, $attributeClasses) instanceof Attribute;
    }
    /**
     * @param string[] $names
     * @return Attribute[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Param $node
     */
    public function findManyByClasses($node, array $names) : array
    {
        $attributes = [];
        foreach ($names as $name) {
            $justFoundAttributes = $this->findManyByClass($node, $name);
            $attributes = \array_merge($attributes, $justFoundAttributes);
        }
        return $attributes;
    }
    private function findArgByName(Attribute $attribute, string $argName) : ?\PhpParser\Node\Expr
    {
        foreach ($attribute->args as $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->name, $argName)) {
                continue;
            }
            return $arg->value;
        }
        return null;
    }
}
