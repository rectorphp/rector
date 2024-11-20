<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class NullableTypeAnalyzer
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveNullableObjectType(Expr $expr) : ?\PHPStan\Type\ObjectType
    {
        $exprType = $this->nodeTypeResolver->getNativeType($expr);
        $baseType = TypeCombinator::removeNull($exprType);
        if (!$baseType instanceof ObjectType) {
            return null;
        }
        return $baseType;
    }
}
