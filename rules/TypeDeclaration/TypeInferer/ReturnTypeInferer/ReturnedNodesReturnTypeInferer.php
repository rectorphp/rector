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
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;
use RectorPrefix20210514\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
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
     * @var \Rector\NodeCollector\NodeCollector\NodeRepository
     */
    private $nodeRepository;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\SilentVoidResolver $silentVoidResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \RectorPrefix20210514\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower, \Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->splArrayFixedTypeNarrower = $splArrayFixedTypeNarrower;
        $this->nodeRepository = $nodeRepository;
    }
    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     */
    public function inferFunctionLike(\PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type
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
     */
    private function collectReturns(\PhpParser\Node\FunctionLike $functionLike) : array
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
    private function resolveNoLocalReturnNodes(\PhpParser\Node\Stmt\ClassLike $classLike, \PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type
    {
        // void type
        if (!$this->isAbstractMethod($classLike, $functionLike)) {
            return new \PHPStan\Type\VoidType();
        }
        return new \PHPStan\Type\MixedType();
    }
    private function isAbstractMethod(\PhpParser\Node\Stmt\ClassLike $classLike, \PhpParser\Node\FunctionLike $functionLike) : bool
    {
        if ($functionLike instanceof \PhpParser\Node\Stmt\ClassMethod && $functionLike->isAbstract()) {
            return \true;
        }
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        return $classLike->isAbstract();
    }
    private function inferFromReturnedMethodCall(\PhpParser\Node\Stmt\Return_ $return, \PhpParser\Node\FunctionLike $originalFunctionLike) : \PHPStan\Type\Type
    {
        if (!$return->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return new \PHPStan\Type\MixedType();
        }
        $classMethod = $this->nodeRepository->findClassMethodByMethodCall($return->expr);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return new \PHPStan\Type\MixedType();
        }
        // avoid infinite looping over self call
        if ($classMethod === $originalFunctionLike) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->inferFunctionLike($classMethod);
    }
    private function isArrayTypeMixed(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        if (!$type->getItemType() instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        return $type->getKeyType() instanceof \PHPStan\Type\MixedType;
    }
    private function correctWithNestedType(\PHPStan\Type\Type $resolvedType, \PhpParser\Node\Stmt\Return_ $return, \PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type
    {
        if ($resolvedType instanceof \PHPStan\Type\MixedType || $this->isArrayTypeMixed($resolvedType)) {
            $correctedType = $this->inferFromReturnedMethodCall($return, $functionLike);
            // override only if has some extra value
            if (!$correctedType instanceof \PHPStan\Type\MixedType) {
                return $correctedType;
            }
        }
        return $resolvedType;
    }
}
