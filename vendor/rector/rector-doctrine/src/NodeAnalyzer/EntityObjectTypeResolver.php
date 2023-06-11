<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\SubtractableType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\Doctrine\TypeAnalyzer\TypeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class EntityObjectTypeResolver
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\TypeFinder
     */
    private $typeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TypeFinder $typeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFinder = $typeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolveFromRepositoryClass(Class_ $repositoryClass) : SubtractableType
    {
        $subtractableType = $this->resolveFromParentConstruct($repositoryClass);
        if (!$subtractableType instanceof MixedType) {
            return $subtractableType;
        }
        $getterReturnType = $this->resolveFromGetterReturnType($repositoryClass);
        if (!$getterReturnType instanceof MixedType) {
            return $getterReturnType;
        }
        return new MixedType();
    }
    private function resolveFromGetterReturnType(Class_ $repositoryClass) : SubtractableType
    {
        foreach ($repositoryClass->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $returnType = $phpDocInfo->getReturnType();
            $objectType = $this->typeFinder->find($returnType, ObjectType::class);
            if (!$objectType instanceof ObjectType) {
                continue;
            }
            return $objectType;
        }
        return new MixedType();
    }
    private function resolveFromParentConstruct(Class_ $class) : SubtractableType
    {
        $constructorClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return new MixedType();
        }
        foreach ((array) $constructorClassMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $argValue = $this->resolveParentConstructSecondArgument($stmt->expr);
            if (!$argValue instanceof ClassConstFetch) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($argValue->name, 'class')) {
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
    private function resolveParentConstructSecondArgument(Expr $expr) : ?Expr
    {
        if (!$expr instanceof StaticCall) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($expr->class, 'parent')) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($expr->name, MethodName::CONSTRUCT)) {
            return null;
        }
        $secondArg = $expr->args[1] ?? null;
        if (!$secondArg instanceof Arg) {
            return null;
        }
        return $secondArg->value;
    }
}
