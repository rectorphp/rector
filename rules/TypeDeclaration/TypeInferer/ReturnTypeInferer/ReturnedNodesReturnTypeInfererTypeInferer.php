<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
/**
 * @deprecated
 * @todo Split into many narrow-focused rules
 */
final class ReturnedNodesReturnTypeInfererTypeInferer implements ReturnTypeInfererInterface
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
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
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
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $reflectionAstResolver;
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(SilentVoidResolver $silentVoidResolver, NodeTypeResolver $nodeTypeResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, TypeFactory $typeFactory, SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower, AstResolver $reflectionAstResolver, NodePrinterInterface $nodePrinter, ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->splArrayFixedTypeNarrower = $splArrayFixedTypeNarrower;
        $this->reflectionAstResolver = $reflectionAstResolver;
        $this->nodePrinter = $nodePrinter;
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function inferFunctionLike(FunctionLike $functionLike) : Type
    {
        $classLike = $this->betterNodeFinder->findParentType($functionLike, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return new MixedType();
        }
        if ($functionLike instanceof ClassMethod && $classLike instanceof Interface_) {
            return new MixedType();
        }
        $types = [];
        $localReturnNodes = $this->collectReturns($functionLike);
        if ($localReturnNodes === []) {
            /** @var Class_|Interface_|Trait_ $classLike */
            return $this->resolveNoLocalReturnNodes($classLike, $functionLike);
        }
        foreach ($localReturnNodes as $localReturnNode) {
            $returnedExprType = $this->nodeTypeResolver->getType($localReturnNode);
            $returnedExprType = $this->correctWithNestedType($returnedExprType, $localReturnNode, $functionLike);
            $types[] = $this->splArrayFixedTypeNarrower->narrow($returnedExprType);
        }
        if ($this->silentVoidResolver->hasSilentVoid($functionLike)) {
            $types[] = new VoidType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    public function getPriority() : int
    {
        return 1000;
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
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
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
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Trait_ $classLike
     * @return \PHPStan\Type\VoidType|\PHPStan\Type\MixedType
     */
    private function resolveNoLocalReturnNodes($classLike, FunctionLike $functionLike)
    {
        // void type
        if (!$this->isAbstractMethod($classLike, $functionLike)) {
            return new VoidType();
        }
        return new MixedType();
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Trait_ $classLike
     */
    private function isAbstractMethod($classLike, FunctionLike $functionLike) : bool
    {
        if ($functionLike instanceof ClassMethod && $functionLike->isAbstract()) {
            return \true;
        }
        if (!$classLike instanceof Class_) {
            return \false;
        }
        return $classLike->isAbstract();
    }
    private function inferFromReturnedMethodCall(Return_ $return, FunctionLike $originalFunctionLike) : Type
    {
        if (!$return->expr instanceof MethodCall) {
            return new MixedType();
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($return->expr);
        if (!$methodReflection instanceof MethodReflection) {
            return new MixedType();
        }
        return $this->resolveClassMethod($methodReflection, $originalFunctionLike);
    }
    private function isArrayTypeMixed(Type $type) : bool
    {
        if (!$type instanceof ArrayType) {
            return \false;
        }
        if (!$type->getItemType() instanceof MixedType) {
            return \false;
        }
        return $type->getKeyType() instanceof MixedType;
    }
    private function correctWithNestedType(Type $resolvedType, Return_ $return, FunctionLike $functionLike) : Type
    {
        if ($resolvedType instanceof MixedType || $this->isArrayTypeMixed($resolvedType)) {
            $correctedType = $this->inferFromReturnedMethodCall($return, $functionLike);
            // override only if has some extra value
            if (!$correctedType instanceof MixedType && !$correctedType instanceof VoidType) {
                return $correctedType;
            }
        }
        return $resolvedType;
    }
    private function resolveClassMethod(MethodReflection $methodReflection, FunctionLike $originalFunctionLike) : Type
    {
        $classMethod = $this->reflectionAstResolver->resolveClassMethodFromMethodReflection($methodReflection);
        if (!$classMethod instanceof ClassMethod) {
            return new MixedType();
        }
        $classMethodCacheKey = $this->nodePrinter->print($classMethod);
        $functionLikeCacheKey = $this->nodePrinter->print($originalFunctionLike);
        if ($classMethodCacheKey === $functionLikeCacheKey) {
            return new MixedType();
        }
        return $this->inferFunctionLike($classMethod);
    }
}
