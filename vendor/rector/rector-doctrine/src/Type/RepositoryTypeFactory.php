<?php

declare (strict_types=1);
namespace Rector\Doctrine\Type;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Type\Generic\GenericObjectType;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class RepositoryTypeFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function createRepositoryPropertyType(\PhpParser\Node\Expr $entityReferenceExpr) : \PHPStan\Type\Generic\GenericObjectType
    {
        if (!$entityReferenceExpr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($entityReferenceExpr->class);
        return new \PHPStan\Type\Generic\GenericObjectType('Doctrine\\ORM\\EntityRepository', [new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($className)]);
    }
}
