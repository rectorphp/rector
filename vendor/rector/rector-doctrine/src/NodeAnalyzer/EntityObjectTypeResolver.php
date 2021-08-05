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
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Rector\Doctrine\TypeAnalyzer\TypeFinder
     */
    private $typeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Doctrine\TypeAnalyzer\TypeFinder $typeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFinder = $typeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolveFromRepositoryClass(\PhpParser\Node\Stmt\Class_ $repositoryClass) : \PHPStan\Type\SubtractableType
    {
        $entityType = $this->resolveFromParentConstruct($repositoryClass);
        if (!$entityType instanceof \PHPStan\Type\MixedType) {
            return $entityType;
        }
        $getterReturnType = $this->resolveFromGetterReturnType($repositoryClass);
        if (!$getterReturnType instanceof \PHPStan\Type\MixedType) {
            return $getterReturnType;
        }
        return new \PHPStan\Type\MixedType();
    }
    private function resolveFromGetterReturnType(\PhpParser\Node\Stmt\Class_ $repositoryClass) : \PHPStan\Type\SubtractableType
    {
        foreach ($repositoryClass->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $returnType = $phpDocInfo->getReturnType();
            $objectType = $this->typeFinder->find($returnType, \PHPStan\Type\ObjectType::class);
            if (!$objectType instanceof \PHPStan\Type\ObjectType) {
                continue;
            }
            return $objectType;
        }
        return new \PHPStan\Type\MixedType();
    }
    private function resolveFromParentConstruct(\PhpParser\Node\Stmt\Class_ $class) : \PHPStan\Type\SubtractableType
    {
        $constructorClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return new \PHPStan\Type\MixedType();
        }
        foreach ((array) $constructorClassMethod->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            $argValue = $this->resolveParentConstructSecondArgument($stmt->expr);
            if (!$argValue instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($argValue->name, 'class')) {
                continue;
            }
            $className = $this->nodeNameResolver->getName($argValue->class);
            if ($className === null) {
                continue;
            }
            return new \PHPStan\Type\ObjectType($className);
        }
        return new \PHPStan\Type\MixedType();
    }
    private function resolveParentConstructSecondArgument(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr
    {
        if (!$expr instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($expr->class, 'parent')) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($expr->name, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        $secondArg = $expr->args[1] ?? null;
        if (!$secondArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        return $secondArg->value;
    }
}
