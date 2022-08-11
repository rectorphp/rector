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
/**
 * @api
 */
final class AttributeFinder
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
     * @param class-string $attributeClass
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function findAttributeByClassArgByName($node, string $attributeClass, string $argName) : ?Expr
    {
        return $this->findAttributeByClassesArgByName($node, [$attributeClass], $argName);
    }
    /**
     * @param class-string[] $attributeClasses
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
     * @param class-string $attributeClass
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
     * @param class-string[] $attributeClasses
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
    private function findArgByName(Attribute $attribute, string $argName) : ?\PhpParser\Node\Expr
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
}
