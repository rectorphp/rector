<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class RepositoryAssignFactory
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver
     */
    private $entityObjectTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver $entityObjectTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->entityObjectTypeResolver = $entityObjectTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * Creates: "$this->repository = $entityManager->getRepository(SomeEntityClass::class)"
     */
    public function create(\PhpParser\Node\Stmt\Class_ $repositoryClass) : \PhpParser\Node\Expr\Assign
    {
        $entityObjectType = $this->entityObjectTypeResolver->resolveFromRepositoryClass($repositoryClass);
        $className = $this->nodeNameResolver->getName($repositoryClass);
        if (!\is_string($className)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $repositoryClassName = $className;
        if (!$entityObjectType instanceof \PHPStan\Type\TypeWithClassName) {
            throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf('An entity was not found for "%s" repository.', $repositoryClassName));
        }
        $classConstFetch = $this->nodeFactory->createClassConstReference($entityObjectType->getClassName());
        $methodCall = $this->nodeFactory->createMethodCall('entityManager', 'getRepository', [$classConstFetch]);
        return $this->nodeFactory->createPropertyAssignmentWithExpr('repository', $methodCall);
    }
}
