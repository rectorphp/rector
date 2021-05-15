<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class RepositoryAssignFactory
{
    /**
     * @var \Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver
     */
    private $entityObjectTypeResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver $entityObjectTypeResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->entityObjectTypeResolver = $entityObjectTypeResolver;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * Creates: "$this->repository = $entityManager->getRepository(SomeEntityClass::class)"
     */
    public function create(\PhpParser\Node\Stmt\Class_ $repositoryClass) : \PhpParser\Node\Expr\Assign
    {
        $entityObjectType = $this->entityObjectTypeResolver->resolveFromRepositoryClass($repositoryClass);
        $repositoryClassName = (string) $repositoryClass->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if (!$entityObjectType instanceof \PHPStan\Type\TypeWithClassName) {
            throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf('An entity was not found for "%s" repository.', $repositoryClassName));
        }
        $classConstFetch = $this->nodeFactory->createClassConstReference($entityObjectType->getClassName());
        $methodCall = $this->nodeFactory->createMethodCall('entityManager', 'getRepository', [$classConstFetch]);
        $methodCall->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE, $repositoryClassName);
        return $this->nodeFactory->createPropertyAssignmentWithExpr('repository', $methodCall);
    }
}
