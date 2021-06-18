<?php

declare(strict_types=1);

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
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ReturnedNodesReturnTypeInferer implements ReturnTypeInfererInterface
{
    public function __construct(
        private SilentVoidResolver $silentVoidResolver,
        private NodeTypeResolver $nodeTypeResolver,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private TypeFactory $typeFactory,
        private SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower,
        private NodeRepository $nodeRepository
    ) {
    }

    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     */
    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        /** @var Class_|Trait_|Interface_|null $classLike */
        $classLike = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return new MixedType();
        }

        if ($functionLike instanceof ClassMethod && $classLike instanceof Interface_) {
            return new MixedType();
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
            $types[] = new VoidType();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    public function getPriority(): int
    {
        return 1000;
    }

    /**
     * @return Return_[]
     */
    private function collectReturns(FunctionLike $functionLike): array
    {
        $returns = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), function (
            Node $node
        ) use (&$returns): ?int {
            // skip Return_ nodes in nested functions or switch statements
            if ($node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if (! $node instanceof Return_) {
                return null;
            }

            $returns[] = $node;

            return null;
        });

        return $returns;
    }

    private function resolveNoLocalReturnNodes(ClassLike $classLike, FunctionLike $functionLike): VoidType | MixedType
    {
        // void type
        if (! $this->isAbstractMethod($classLike, $functionLike)) {
            return new VoidType();
        }

        return new MixedType();
    }

    private function isAbstractMethod(ClassLike $classLike, FunctionLike $functionLike): bool
    {
        if ($functionLike instanceof ClassMethod && $functionLike->isAbstract()) {
            return true;
        }

        if (! $classLike instanceof Class_) {
            return false;
        }
        return $classLike->isAbstract();
    }

    private function inferFromReturnedMethodCall(Return_ $return, FunctionLike $originalFunctionLike): Type
    {
        if (! $return->expr instanceof MethodCall) {
            return new MixedType();
        }

        $classMethod = $this->nodeRepository->findClassMethodByMethodCall($return->expr);
        if (! $classMethod instanceof ClassMethod) {
            return new MixedType();
        }

        // avoid infinite looping over self call
        if ($classMethod === $originalFunctionLike) {
            return new MixedType();
        }

        return $this->inferFunctionLike($classMethod);
    }

    private function isArrayTypeMixed(Type $type): bool
    {
        if (! $type instanceof ArrayType) {
            return false;
        }

        if (! $type->getItemType() instanceof MixedType) {
            return false;
        }

        return $type->getKeyType() instanceof MixedType;
    }

    private function correctWithNestedType(Type $resolvedType, Return_ $return, FunctionLike $functionLike): Type
    {
        if ($resolvedType instanceof MixedType || $this->isArrayTypeMixed($resolvedType)) {
            $correctedType = $this->inferFromReturnedMethodCall($return, $functionLike);
            // override only if has some extra value
            if (! $correctedType instanceof MixedType) {
                return $correctedType;
            }
        }

        return $resolvedType;
    }
}
