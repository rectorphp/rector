<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\TypeResolver;

use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
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
        if ($arrayDimFetch->dim !== null) {
            return $this->nodeTypeResolver->getType($arrayDimFetch->dim);
        }
        return new MixedType();
    }
    private function resolveValueStaticType(ArrayDimFetch $arrayDimFetch) : Type
    {
        $parentParent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentParent instanceof Assign) {
            return $this->nodeTypeResolver->getType($parentParent->expr);
        }
        return new MixedType();
    }
}
