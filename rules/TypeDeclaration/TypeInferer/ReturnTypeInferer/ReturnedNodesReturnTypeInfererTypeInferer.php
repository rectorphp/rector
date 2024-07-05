<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
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
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower
     */
    private $splArrayFixedTypeNarrower;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
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
        $localReturnNodes = $this->betterNodeFinder->findReturnsScoped($functionLike);
        if ($localReturnNodes === []) {
            return $this->resolveNoLocalReturnNodes($functionLike, $classReflection);
        }
        foreach ($localReturnNodes as $localReturnNode) {
            $returnedExprType = $localReturnNode->expr instanceof Expr ? $this->nodeTypeResolver->getNativeType($localReturnNode->expr) : new VoidType();
            $types[] = $this->splArrayFixedTypeNarrower->narrow($returnedExprType);
        }
        if ($this->silentVoidResolver->hasSilentVoid($functionLike)) {
            $types[] = new VoidType();
        }
        return $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($types);
    }
    /**
     * @return \PHPStan\Type\VoidType|\PHPStan\Type\MixedType
     */
    private function resolveNoLocalReturnNodes(FunctionLike $functionLike, ?ClassReflection $classReflection)
    {
        // void type
        if (!$this->isAbstractMethod($functionLike, $classReflection)) {
            return new VoidType();
        }
        return new MixedType();
    }
    private function isAbstractMethod(FunctionLike $functionLike, ?ClassReflection $classReflection) : bool
    {
        if ($functionLike instanceof ClassMethod && $functionLike->isAbstract()) {
            return \true;
        }
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if (!$classReflection->isClass()) {
            return \false;
        }
        return $classReflection->isAbstract();
    }
}
