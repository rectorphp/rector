<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassMethodReturnTypeOverrideGuard
{
    /**
     * @var array<string, array<string>>
     */
    private const CHAOTIC_CLASS_METHOD_NAMES = [
        NodeVisitor::class => ['enterNode', 'leaveNode', 'beforeTraverse', 'afterTraverse'],
    ];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        NodeRepository $nodeRepository,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeRepository = $nodeRepository;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        // 1. skip magic methods
        if ($this->nodeNameResolver->isName($classMethod->name, '__*')) {
            return true;
        }

        // 2. skip chaotic contract class methods
        if ($this->shouldSkipChaoticClassMethods($classMethod)) {
            return true;
        }

        // 3. skip has children and current has no return
        $class = $classMethod->getAttribute(AttributeKey::PARENT_NODE);
        $hasChildren = $class instanceof Class_ && $this->nodeRepository->hasClassChildren($class);
        if (! $hasChildren) {
            return false;
        }

        $hasReturn = (bool) $this->betterNodeFinder->findFirst(
            (array) $classMethod->stmts,
            function (Node $node): bool {
                if (! $node instanceof Return_) {
                    return false;
                }
                return $node->expr instanceof Expr;
            }
        );

        if ($hasReturn) {
            return false;
        }

        return $classMethod->returnType === null;
    }

    public function shouldSkipClassMethodOldTypeWithNewType(Type $oldType, Type $newType): bool
    {
        if ($oldType instanceof MixedType) {
            return false;
        }

        if ($oldType->isSuperTypeOf($newType)->yes()) {
            return true;
        }

        return $this->isArrayMutualType($newType, $oldType);
    }

    private function shouldSkipChaoticClassMethods(ClassMethod $classMethod): bool
    {
        /** @var string|null $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        foreach (self::CHAOTIC_CLASS_METHOD_NAMES as $chaoticClass => $chaoticMethodNames) {
            if (! is_a($className, $chaoticClass, true)) {
                continue;
            }

            return in_array($methodName, $chaoticMethodNames, true);
        }

        return false;
    }

    private function isArrayMutualType(Type $newType, Type $oldType): bool
    {
        if (! $newType instanceof ArrayType) {
            return false;
        }

        if (! $oldType instanceof ArrayType) {
            return false;
        }

        $oldTypeWithClassName = $oldType->getItemType();
        if (! $oldTypeWithClassName instanceof TypeWithClassName) {
            return false;
        }

        $arrayItemType = $newType->getItemType();
        if (! $arrayItemType instanceof UnionType) {
            return false;
        }

        $isMatchingClassTypes = false;

        foreach ($arrayItemType->getTypes() as $newUnionedType) {
            if (! $newUnionedType instanceof TypeWithClassName) {
                return false;
            }

            $oldClass = $this->nodeTypeResolver->getFullyQualifiedClassName($oldTypeWithClassName);
            $newClass = $this->nodeTypeResolver->getFullyQualifiedClassName($newUnionedType);

            if (is_a($oldClass, $newClass, true) || is_a($newClass, $oldClass, true)) {
                $isMatchingClassTypes = true;
            } else {
                return false;
            }
        }

        return $isMatchingClassTypes;
    }
}
