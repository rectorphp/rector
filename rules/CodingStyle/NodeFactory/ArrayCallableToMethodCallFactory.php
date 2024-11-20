<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeFactory;

use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
final class ArrayCallableToMethodCallFactory
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function create(Array_ $array) : ?MethodCall
    {
        if (\count($array->items) !== 2) {
            return null;
        }
        $firstItem = $array->items[0];
        $secondItem = $array->items[1];
        if (!$firstItem instanceof ArrayItem) {
            return null;
        }
        if (!$secondItem instanceof ArrayItem) {
            return null;
        }
        if (!$secondItem->value instanceof String_) {
            return null;
        }
        if (!$firstItem->value instanceof PropertyFetch && !$firstItem->value instanceof Variable) {
            return null;
        }
        $firstItemType = $this->nodeTypeResolver->getType($firstItem->value);
        $className = ClassNameFromObjectTypeResolver::resolve($firstItemType);
        if ($className === null) {
            return null;
        }
        $string = $secondItem->value;
        $methodName = $string->value;
        return new MethodCall($firstItem->value, $methodName);
    }
}
