<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\NodeFactory;

use Doctrine\ORM\EntityManagerInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class DoctrineNodeFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(BuilderFactory $builderFactory, DocBlockManipulator $docBlockManipulator)
    {
        $this->builderFactory = $builderFactory;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * Creates:
     * $this->repository = $entityManager->getRepository(\EntityClass::class);
     */
    public function createRepositoryAssign(string $entityClass): Assign
    {
        $repositoryPropertyFetch = new PropertyFetch(new Variable('this'), new Identifier('repository'));

        $entityClassReference = new ClassConstFetch(new FullyQualified($entityClass), 'class');

        $getRepositoryMethodCall = new MethodCall(new Variable('entityManager'), 'getRepository', [
            new Arg($entityClassReference),
        ]);

        return new Assign($repositoryPropertyFetch, $getRepositoryMethodCall);
    }

    public function createRepositoryProperty(): Property
    {
        $repositoryProperty = $this->builderFactory->property('repository')
            ->makePrivate()
            ->getNode();

        $this->docBlockManipulator->changeVarTag(
            $repositoryProperty,
            new FullyQualifiedObjectType('Doctrine\ORM\EntityRepository')
        );

        return $repositoryProperty;
    }

    public function createConstructorWithGetRepositoryAssign(string $entityClass): ClassMethod
    {
        $param = $this->builderFactory->param('entityManager')
            ->setType(new FullyQualified(EntityManagerInterface::class))
            ->getNode();

        $assign = $this->createRepositoryAssign($entityClass);

        return $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($param)
            ->addStmt($assign)
            ->getNode();
    }
}
