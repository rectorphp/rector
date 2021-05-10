<?php

declare (strict_types=1);
namespace Rector\Php71\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class EmptyStringDefaultPropertyFinder
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var array<string, PropertyProperty[]>
     */
    private $propertyPropertiesByClassName = [];
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return PropertyProperty[]
     */
    public function find(\PhpParser\Node\Expr\Assign $assign) : array
    {
        $classLike = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return [];
        }
        $className = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if (!\is_string($className)) {
            return [];
        }
        if (isset($this->propertyPropertiesByClassName[$className])) {
            return $this->propertyPropertiesByClassName[$className];
        }
        /** @var PropertyProperty[] $propertyProperties */
        $propertyProperties = $this->betterNodeFinder->find($classLike, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Stmt\PropertyProperty) {
                return \false;
            }
            if ($node->default === null) {
                return \false;
            }
            return $this->isEmptyString($node->default);
        });
        $this->propertyPropertiesByClassName[$className] = $propertyProperties;
        return $propertyProperties;
    }
    private function isEmptyString(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        return $expr->value === '';
    }
}
