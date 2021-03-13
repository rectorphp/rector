<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\DoctrineCodeQuality\NodeAnalyzer\EntityObjectTypeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class RepositoryAssignFactory
{
    /**
     * @var EntityObjectTypeResolver
     */
    private $entityObjectTypeResolver;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(EntityObjectTypeResolver $entityObjectTypeResolver, NodeFactory $nodeFactory)
    {
        $this->entityObjectTypeResolver = $entityObjectTypeResolver;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * Creates:
     * "$this->repository = $entityManager->getRepository(SomeEntityClass::class)"
     */
    public function create(Class_ $repositoryClass): Assign
    {
        $entityObjectType = $this->entityObjectTypeResolver->resolveFromRepositoryClass($repositoryClass);
        $repositoryClassName = (string) $repositoryClass->getAttribute(AttributeKey::CLASS_NAME);

        if (! $entityObjectType instanceof TypeWithClassName) {
            throw new ShouldNotHappenException(sprintf(
                'An entity was not found for "%s" repository.',
                $repositoryClassName,
            ));
        }

        $classConstFetch = $this->nodeFactory->createClassConstReference($entityObjectType->getClassName());

        $methodCall = $this->nodeFactory->createMethodCall('entityManager', 'getRepository', [$classConstFetch]);
        $methodCall->setAttribute(AttributeKey::CLASS_NODE, $repositoryClassName);

        return $this->nodeFactory->createPropertyAssignmentWithExpr('repository', $methodCall);
    }
}
