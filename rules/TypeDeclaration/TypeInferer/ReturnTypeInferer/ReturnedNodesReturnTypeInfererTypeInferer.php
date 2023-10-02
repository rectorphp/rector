<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
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
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
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
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver, NodeTypeResolver $nodeTypeResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, TypeFactory $typeFactory, SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower, ReflectionResolver $reflectionResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->splArrayFixedTypeNarrower = $splArrayFixedTypeNarrower;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function inferFunctionLike(FunctionLike $functionLike) : Type
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($functionLike);
        if (!$classReflection instanceof ClassReflection) {
            return new MixedType();
        }
        if ($functionLike instanceof ClassMethod && $classReflection->isInterface()) {
            return new MixedType();
        }
        $types = [];
        $localReturnNodes = $this->collectReturns($functionLike);
        if ($localReturnNodes === []) {
            return $this->resolveNoLocalReturnNodes($classReflection, $functionLike);
        }
        foreach ($localReturnNodes as $localReturnNode) {
            $returnedExprType = $localReturnNode->expr instanceof Expr ? $this->nodeTypeResolver->getNativeType($localReturnNode->expr) : new VoidType();
            $types[] = $this->splArrayFixedTypeNarrower->narrow($returnedExprType);
        }
        if ($this->silentVoidResolver->hasSilentVoid($functionLike)) {
            $types[] = new VoidType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    /**
     * @return Return_[]
     */
    private function collectReturns(FunctionLike $functionLike) : array
    {
        $returns = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), static function (Node $node) use(&$returns) : ?int {
            // skip Return_ nodes in nested functions or switch statements
            if ($node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Return_) {
                return null;
            }
            $returns[] = $node;
            return null;
        });
        return $returns;
    }
    /**
     * @return \PHPStan\Type\VoidType|\PHPStan\Type\MixedType
     */
    private function resolveNoLocalReturnNodes(ClassReflection $classReflection, FunctionLike $functionLike)
    {
        // void type
        if (!$this->isAbstractMethod($classReflection, $functionLike)) {
            return new VoidType();
        }
        return new MixedType();
    }
    private function isAbstractMethod(ClassReflection $classReflection, FunctionLike $functionLike) : bool
    {
        if ($functionLike instanceof ClassMethod && $functionLike->isAbstract()) {
            return \true;
        }
        if (!$classReflection->isClass()) {
            return \false;
        }
        return $classReflection->isAbstract();
    }
}
