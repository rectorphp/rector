<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;
use RectorPrefix20210705\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ReturnedNodesReturnTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface
{
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower
     */
    private $splArrayFixedTypeNarrower;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $reflectionAstResolver;
    /**
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\SilentVoidResolver $silentVoidResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \RectorPrefix20210705\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower, \Rector\Core\PhpParser\AstResolver $reflectionAstResolver, \Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->splArrayFixedTypeNarrower = $splArrayFixedTypeNarrower;
        $this->reflectionAstResolver = $reflectionAstResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     */
    public function inferFunctionLike($functionLike) : \PHPStan\Type\Type
    {
        /** @var Class_|Trait_|Interface_|null $classLike */
        $classLike = $functionLike->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return new \PHPStan\Type\MixedType();
        }
        if ($functionLike instanceof \PhpParser\Node\Stmt\ClassMethod && $classLike instanceof \PhpParser\Node\Stmt\Interface_) {
            return new \PHPStan\Type\MixedType();
        }
        $types = [];
        $localReturnNodes = $this->collectReturns($functionLike);
        if ($localReturnNodes === []) {
            return $this->resolveNoLocalReturnNodes($classLike, $functionLike);
        }
        foreach ($localReturnNodes as $localReturnNode) {
            $returnedExprType = $this->nodeTypeResolver->getStaticType($localReturnNode);
            $returnedExprType = $this->correctWithNestedType($returnedExprType, $localReturnNode, $functionLike);
            $types[] = $this->splArrayFixedTypeNarrower->narrow($returnedExprType);
        }
        if ($this->silentVoidResolver->hasSilentVoid($functionLike)) {
            $types[] = new \PHPStan\Type\VoidType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    public function getPriority() : int
    {
        return 1000;
    }
    /**
     * @return Return_[]
     * @param \PhpParser\Node\FunctionLike $functionLike
     */
    private function collectReturns($functionLike) : array
    {
        $returns = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), function (\PhpParser\Node $node) use(&$returns) : ?int {
            // skip Return_ nodes in nested functions or switch statements
            if ($node instanceof \PhpParser\Node\FunctionLike) {
                return \PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            if (!$node instanceof \PhpParser\Node\Stmt\Return_) {
                return null;
            }
            $returns[] = $node;
            return null;
        });
        return $returns;
    }
    /**
     * @return \PHPStan\Type\VoidType|\PHPStan\Type\MixedType
     * @param \PhpParser\Node\Stmt\ClassLike $classLike
     * @param \PhpParser\Node\FunctionLike $functionLike
     */
    private function resolveNoLocalReturnNodes($classLike, $functionLike)
    {
        // void type
        if (!$this->isAbstractMethod($classLike, $functionLike)) {
            return new \PHPStan\Type\VoidType();
        }
        return new \PHPStan\Type\MixedType();
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassLike $classLike
     * @param \PhpParser\Node\FunctionLike $functionLike
     */
    private function isAbstractMethod($classLike, $functionLike) : bool
    {
        if ($functionLike instanceof \PhpParser\Node\Stmt\ClassMethod && $functionLike->isAbstract()) {
            return \true;
        }
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        return $classLike->isAbstract();
    }
    /**
     * @param \PhpParser\Node\Stmt\Return_ $return
     * @param \PhpParser\Node\FunctionLike $originalFunctionLike
     */
    private function inferFromReturnedMethodCall($return, $originalFunctionLike) : \PHPStan\Type\Type
    {
        if (!$return->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return new \PHPStan\Type\MixedType();
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($return->expr);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->resolveClassMethod($methodReflection, $originalFunctionLike);
    }
    /**
     * @param \PHPStan\Type\Type $type
     */
    private function isArrayTypeMixed($type) : bool
    {
        if (!$type instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        if (!$type->getItemType() instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        return $type->getKeyType() instanceof \PHPStan\Type\MixedType;
    }
    /**
     * @param \PHPStan\Type\Type $resolvedType
     * @param \PhpParser\Node\Stmt\Return_ $return
     * @param \PhpParser\Node\FunctionLike $functionLike
     */
    private function correctWithNestedType($resolvedType, $return, $functionLike) : \PHPStan\Type\Type
    {
        if ($resolvedType instanceof \PHPStan\Type\MixedType || $this->isArrayTypeMixed($resolvedType)) {
            $correctedType = $this->inferFromReturnedMethodCall($return, $functionLike);
            // override only if has some extra value
            if (!$correctedType instanceof \PHPStan\Type\MixedType && !$correctedType instanceof \PHPStan\Type\VoidType) {
                return $correctedType;
            }
        }
        return $resolvedType;
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection $methodReflection
     * @param \PhpParser\Node\FunctionLike $originalFunctionLike
     */
    private function resolveClassMethod($methodReflection, $originalFunctionLike) : \PHPStan\Type\Type
    {
        $classMethod = $this->reflectionAstResolver->resolveClassMethodFromMethodReflection($methodReflection);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return new \PHPStan\Type\MixedType();
        }
        $classMethodCacheKey = $this->betterStandardPrinter->print($classMethod);
        $functionLikeCacheKey = $this->betterStandardPrinter->print($originalFunctionLike);
        if ($classMethodCacheKey === $functionLikeCacheKey) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->inferFunctionLike($classMethod);
    }
}
