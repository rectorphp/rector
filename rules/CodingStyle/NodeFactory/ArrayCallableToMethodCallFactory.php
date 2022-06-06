<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
final class ArrayCallableToMethodCallFactory
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
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
        if (!$firstItemType instanceof TypeWithClassName) {
            return null;
        }
        $string = $secondItem->value;
        $methodName = $string->value;
        return new MethodCall($firstItem->value, $methodName);
    }
}
