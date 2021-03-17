<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\MethodName;
use Rector\DoctrineCodeQuality\TypeAnalyzer\TypeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class EntityObjectTypeResolver
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var TypeFinder
     */
    private $typeFinder;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        TypeFinder $typeFinder,
        NodeRepository $nodeRepository,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFinder = $typeFinder;
        $this->nodeRepository = $nodeRepository;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function resolveFromRepositoryClass(Class_ $repositoryClass): Type
    {
        $entityType = $this->resolveFromParentConstruct($repositoryClass);
        if (! $entityType instanceof MixedType) {
            return $entityType;
        }

        $getterReturnType = $this->resolveFromGetterReturnType($repositoryClass);
        if (! $getterReturnType instanceof MixedType) {
            return $getterReturnType;
        }

        $entityType = $this->resolveFromMatchingEntityAnnotation($repositoryClass);
        if (! $entityType instanceof MixedType) {
            return $entityType;
        }

        return new MixedType();
    }

    private function resolveFromGetterReturnType(Class_ $repositoryClass): Type
    {
        foreach ($repositoryClass->getMethods() as $classMethod) {
            if (! $classMethod->isPublic()) {
                continue;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

            $returnType = $phpDocInfo->getReturnType();
            $objectType = $this->typeFinder->find($returnType, ObjectType::class);
            if (! $objectType instanceof ObjectType) {
                continue;
            }

            return $objectType;
        }

        return new MixedType();
    }

    private function resolveFromMatchingEntityAnnotation(Class_ $repositoryClass): Type
    {
        $repositoryClassName = $repositoryClass->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($this->nodeRepository->getClasses() as $class) {
            if ($class->isFinal()) {
                continue;
            }

            if ($class->isAbstract()) {
                continue;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
            if (! $phpDocInfo->hasByType(EntityTagValueNode::class)) {
                continue;
            }

            /** @var EntityTagValueNode $entityTagValueNode */
            $entityTagValueNode = $phpDocInfo->getByType(EntityTagValueNode::class);
            if ($entityTagValueNode->getRepositoryClass() !== $repositoryClassName) {
                continue;
            }

            $className = $this->nodeNameResolver->getName($class);
            if (! is_string($className)) {
                throw new ShouldNotHappenException();
            }

            return new ObjectType($className);
        }

        return new MixedType();
    }

    private function resolveFromParentConstruct(Class_ $class): Type
    {
        $constructorClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $constructorClassMethod instanceof ClassMethod) {
            return new MixedType();
        }

        foreach ((array) $constructorClassMethod->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $argValue = $this->resolveParentConstructSecondArgument($stmt->expr);
            if (! $argValue instanceof ClassConstFetch) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($argValue->name, 'class')) {
                continue;
            }

            $className = $this->nodeNameResolver->getName($argValue->class);
            if ($className === null) {
                continue;
            }

            return new ObjectType($className);
        }

        return new MixedType();
    }

    private function resolveParentConstructSecondArgument(Expr $expr): ?Expr
    {
        if (! $expr instanceof StaticCall) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($expr->class, 'parent')) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($expr->name, MethodName::CONSTRUCT)) {
            return null;
        }

        $secondArg = $expr->args[1] ?? null;
        if (! $secondArg instanceof Arg) {
            return null;
        }

        return $secondArg->value;
    }
}
