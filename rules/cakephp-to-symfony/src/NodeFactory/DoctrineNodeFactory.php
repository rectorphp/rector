<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeFactory;

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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class DoctrineNodeFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(BuilderFactory $builderFactory, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->builderFactory = $builderFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function createRepositoryProperty(): Property
    {
        $repositoryPropertyBuilder = $this->builderFactory->property('repository');
        $repositoryPropertyBuilder->makePrivate();

        $repositoryProperty = $repositoryPropertyBuilder->getNode();

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($repositoryProperty);
        $phpDocInfo->changeVarType(new FullyQualifiedObjectType('Doctrine\ORM\EntityRepository'));

        return $repositoryProperty;
    }

    public function createConstructorWithGetRepositoryAssign(string $entityClass): ClassMethod
    {
        $paramBuilder = $this->builderFactory->param('entityManager');
        $paramBuilder->setType(new FullyQualified(EntityManagerInterface::class));

        $param = $paramBuilder->getNode();

        $assign = $this->createRepositoryAssign($entityClass);

        return $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($param)
            ->addStmt($assign)
            ->getNode();
    }

    /**
     * Creates:
     * $this->repository = $entityManager->getRepository(\EntityClass::class);
     */
    private function createRepositoryAssign(string $entityClass): Assign
    {
        $repositoryPropertyFetch = new PropertyFetch(new Variable('this'), new Identifier('repository'));

        $entityClassReference = new ClassConstFetch(new FullyQualified($entityClass), 'class');

        $getRepositoryMethodCall = new MethodCall(new Variable('entityManager'), 'getRepository', [
            new Arg($entityClassReference),
        ]);

        return new Assign($repositoryPropertyFetch, $getRepositoryMethodCall);
    }
}
