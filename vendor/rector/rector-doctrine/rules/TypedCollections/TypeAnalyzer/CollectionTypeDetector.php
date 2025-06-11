<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\TypeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class CollectionTypeDetector
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isCollectionNonNullableType(Expr $expr) : bool
    {
        $exprType = $this->nodeTypeResolver->getType($expr);
        return $this->isCollectionObjectType($exprType);
    }
    public function isCollectionType(Expr $expr) : bool
    {
        $exprType = $this->nodeTypeResolver->getType($expr);
        if ($exprType instanceof IntersectionType) {
            foreach ($exprType->getTypes() as $intersectionedType) {
                if ($this->isCollectionObjectType($intersectionedType)) {
                    return \true;
                }
            }
        }
        if ($exprType instanceof UnionType) {
            $bareExprType = TypeCombinator::removeNull($exprType);
            return $this->isCollectionObjectType($bareExprType);
        }
        return $this->isCollectionObjectType($exprType);
    }
    private function isCollectionObjectType(Type $exprType) : bool
    {
        if (!$exprType instanceof ObjectType) {
            return \false;
        }
        return $exprType->isInstanceOf(DoctrineClass::COLLECTION)->yes();
    }
}
