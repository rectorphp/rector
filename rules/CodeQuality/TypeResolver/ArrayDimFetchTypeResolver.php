<?php

declare (strict_types=1);
namespace Rector\CodeQuality\TypeResolver;

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
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolve(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : \PHPStan\Type\ArrayType
    {
        $keyStaticType = $this->resolveDimType($arrayDimFetch);
        $valueStaticType = $this->resolveValueStaticType($arrayDimFetch);
        return new \PHPStan\Type\ArrayType($keyStaticType, $valueStaticType);
    }
    private function resolveDimType(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : \PHPStan\Type\Type
    {
        if ($arrayDimFetch->dim !== null) {
            return $this->nodeTypeResolver->getType($arrayDimFetch->dim);
        }
        return new \PHPStan\Type\MixedType();
    }
    private function resolveValueStaticType(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : \PHPStan\Type\Type
    {
        $parentParent = $arrayDimFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentParent instanceof \PhpParser\Node\Expr\Assign) {
            return $this->nodeTypeResolver->getType($parentParent->expr);
        }
        return new \PHPStan\Type\MixedType();
    }
}
