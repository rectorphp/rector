<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
final class CallTypesResolver
{
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
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeFactory $typeFactory, ReflectionProvider $reflectionProvider, BetterNodeFinder $betterNodeFinder, AstResolver $astResolver, NodeComparator $nodeComparator)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->astResolver = $astResolver;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $calls
     * @return array<int, Type>
     */
    public function resolveStrictTypesFromCalls(array $calls) : array
    {
        $staticTypesByArgumentPosition = [];
        foreach ($calls as $call) {
            if (!$call instanceof StaticCall && !$call instanceof MethodCall) {
                continue;
            }
            if ($this->isRecursiveCall($call)) {
                return [];
            }
            foreach ($call->args as $position => $arg) {
                if (!$arg instanceof Arg) {
                    continue;
                }
                $staticTypesByArgumentPosition[$position][] = $this->resolveStrictArgValueType($arg);
            }
        }
        // unite to single type
        return $this->unionToSingleType($staticTypesByArgumentPosition);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function isRecursiveCall($call) : bool
    {
        $parentClassMethod = $this->betterNodeFinder->findParentType($call, ClassMethod::class);
        if (!$parentClassMethod instanceof ClassMethod) {
            return \false;
        }
        $classMethod = $this->astResolver->resolveClassMethodFromCall($call);
        return $this->nodeComparator->areNodesEqual($parentClassMethod, $classMethod);
    }
    private function resolveStrictArgValueType(Arg $arg) : Type
    {
        $argValueType = $this->nodeTypeResolver->getNativeType($arg->value);
        // "self" in another object is not correct, this make it independent
        $argValueType = $this->correctSelfType($argValueType);
        if (!$argValueType instanceof ObjectType) {
            return $argValueType;
        }
        // fix false positive generic type on string
        if (!$this->reflectionProvider->hasClass($argValueType->getClassName())) {
            return new MixedType();
        }
        return $argValueType;
    }
    private function correctSelfType(Type $argValueType) : Type
    {
        if ($argValueType instanceof ThisType) {
            return new ObjectType($argValueType->getClassName());
        }
        return $argValueType;
    }
    /**
     * @param array<int, Type[]> $staticTypesByArgumentPosition
     * @return array<int, Type>
     */
    private function unionToSingleType(array $staticTypesByArgumentPosition) : array
    {
        $staticTypeByArgumentPosition = [];
        foreach ($staticTypesByArgumentPosition as $position => $staticTypes) {
            $unionedType = $this->typeFactory->createMixedPassedOrUnionType($staticTypes);
            // narrow parents to most child type
            $staticTypeByArgumentPosition[$position] = $this->narrowParentObjectTreeToSingleObjectChildType($unionedType);
        }
        if (\count($staticTypeByArgumentPosition) !== 1) {
            return $staticTypeByArgumentPosition;
        }
        if (!$staticTypeByArgumentPosition[0] instanceof NullType) {
            return $staticTypeByArgumentPosition;
        }
        return [new MixedType()];
    }
    private function narrowParentObjectTreeToSingleObjectChildType(Type $type) : Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        if (!$this->isTypeWithClassNameOnly($type)) {
            return $type;
        }
        /** @var TypeWithClassName $firstUnionedType */
        $firstUnionedType = $type->getTypes()[0];
        foreach ($type->getTypes() as $unionedType) {
            if (!$unionedType instanceof TypeWithClassName) {
                return $type;
            }
            if ($unionedType->isSuperTypeOf($firstUnionedType)->yes()) {
                return $type;
            }
        }
        return $firstUnionedType;
    }
    private function isTypeWithClassNameOnly(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof TypeWithClassName) {
                return \false;
            }
        }
        return \true;
    }
}
