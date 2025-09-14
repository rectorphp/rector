<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\TypeResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class YieldTypeResolver
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver, TypeFactory $typeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param array<Yield_|YieldFrom> $yieldNodes
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType
     */
    public function resolveFromYieldNodes(array $yieldNodes, $functionLike)
    {
        $yieldedTypes = $this->resolveYieldedTypes($yieldNodes);
        $className = $this->resolveClassName($functionLike);
        if ($yieldedTypes === []) {
            return new FullyQualifiedObjectType($className);
        }
        $yieldedTypes = $this->typeFactory->createMixedPassedOrUnionType($yieldedTypes);
        return new FullyQualifiedGenericObjectType($className, [$yieldedTypes]);
    }
    /**
     * @param \PhpParser\Node\Expr\Yield_|\PhpParser\Node\Expr\YieldFrom $yield
     */
    private function resolveYieldValue($yield): ?Expr
    {
        if ($yield instanceof Yield_) {
            return $yield->value;
        }
        return $yield->expr;
    }
    /**
     * @param array<Yield_|YieldFrom> $yieldNodes
     * @return Type[]
     */
    private function resolveYieldedTypes(array $yieldNodes): array
    {
        $yieldedTypes = [];
        foreach ($yieldNodes as $yieldNode) {
            $value = $this->resolveYieldValue($yieldNode);
            if (!$value instanceof Expr) {
                // one of the yields is empty
                return [];
            }
            $resolvedType = $this->nodeTypeResolver->getType($value);
            if ($resolvedType instanceof MixedType) {
                continue;
            }
            $yieldedTypes[] = $resolvedType;
        }
        return $yieldedTypes;
    }
    /**
     * @param \PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function resolveClassName($functionLike): string
    {
        $returnTypeNode = $functionLike->getReturnType();
        if ($returnTypeNode instanceof Identifier && $returnTypeNode->name === 'iterable') {
            return 'Iterator';
        }
        if ($returnTypeNode instanceof Name && !$this->nodeNameResolver->isName($returnTypeNode, 'Generator')) {
            return $this->nodeNameResolver->getName($returnTypeNode);
        }
        return 'Generator';
    }
}
