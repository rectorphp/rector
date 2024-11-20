<?php

declare (strict_types=1);
namespace Rector\CodeQuality\TypeResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ArrayDimFetchTypeResolver
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolve(ArrayDimFetch $arrayDimFetch, Assign $assign) : ArrayType
    {
        $keyStaticType = $this->resolveDimType($arrayDimFetch);
        $valueStaticType = $this->nodeTypeResolver->getType($assign->expr);
        return new ArrayType($keyStaticType, $valueStaticType);
    }
    private function resolveDimType(ArrayDimFetch $arrayDimFetch) : Type
    {
        if ($arrayDimFetch->dim instanceof Expr) {
            return $this->nodeTypeResolver->getType($arrayDimFetch->dim);
        }
        return new MixedType();
    }
}
