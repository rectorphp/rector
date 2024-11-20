<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Reflection\ReflectionResolver;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;
/**
 * @internal
 */
final class ReturnedNodesReturnTypeInfererTypeInferer
{
    /**
     * @readonly
     */
    private SilentVoidResolver $silentVoidResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver, BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, TypeFactory $typeFactory, SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower, ReflectionResolver $reflectionResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
        $this->splArrayFixedTypeNarrower = $splArrayFixedTypeNarrower;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function inferFunctionLike($functionLike) : Type
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($functionLike);
        if ($functionLike instanceof ClassMethod && (!$classReflection instanceof ClassReflection || $classReflection->isInterface())) {
            return new MixedType();
        }
        $types = [];
        // empty returns can have yield, use MixedType() instead
        $localReturnNodes = $this->betterNodeFinder->findReturnsScoped($functionLike);
        if ($localReturnNodes === []) {
            return new MixedType();
        }
        $hasVoid = \false;
        foreach ($localReturnNodes as $localReturnNode) {
            if (!$localReturnNode->expr instanceof Expr) {
                $hasVoid = \true;
                $types[] = new VoidType();
                continue;
            }
            $returnedExprType = $this->nodeTypeResolver->getNativeType($localReturnNode->expr);
            $types[] = $this->splArrayFixedTypeNarrower->narrow($returnedExprType);
        }
        if (!$hasVoid && $this->silentVoidResolver->hasSilentVoid($functionLike)) {
            $types[] = new VoidType();
        }
        $returnType = $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($types);
        // only void?
        if ($returnType->isVoid()->yes()) {
            return new MixedType();
        }
        return $returnType;
    }
}
