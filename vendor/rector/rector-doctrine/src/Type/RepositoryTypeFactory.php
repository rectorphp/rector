<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\Type;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericObjectType;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class RepositoryTypeFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function createRepositoryPropertyType(Expr $entityReferenceExpr) : GenericObjectType
    {
        if (!$entityReferenceExpr instanceof ClassConstFetch) {
            throw new NotImplementedYetException();
        }
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($entityReferenceExpr->class);
        return new GenericObjectType('Doctrine\\ORM\\EntityRepository', [new FullyQualifiedObjectType($className)]);
    }
}
