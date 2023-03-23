<?php

declare (strict_types=1);
namespace Rector\CodeQuality\TypeResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ArrayDimFetchTypeResolver
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
    public function resolve(ArrayDimFetch $arrayDimFetch) : ArrayType
    {
        $keyStaticType = $this->resolveDimType($arrayDimFetch);
        $valueStaticType = $this->resolveValueStaticType($arrayDimFetch);
        return new ArrayType($keyStaticType, $valueStaticType);
    }
    private function resolveDimType(ArrayDimFetch $arrayDimFetch) : Type
    {
        if ($arrayDimFetch->dim instanceof Expr) {
            return $this->nodeTypeResolver->getType($arrayDimFetch->dim);
        }
        return new MixedType();
    }
    private function resolveValueStaticType(ArrayDimFetch $arrayDimFetch) : Type
    {
        $parentNode = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            return $this->nodeTypeResolver->getType($parentNode->expr);
        }
        return new MixedType();
    }
}
