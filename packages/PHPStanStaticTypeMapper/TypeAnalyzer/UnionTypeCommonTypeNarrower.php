<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;
use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantStringType;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericClassStringType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector;
final class UnionTypeCommonTypeNarrower
{
    /**
     * Key = the winner Array = the group of types matched
     *
     * @var array<string, array<class-string<Node>|class-string<\PHPStan\PhpDocParser\Ast\Node>|class-string<RectorInterface>>>
     */
    private const PRIORITY_TYPES = [ClassLike::class => [ClassLike::class], FunctionLike::class => [FunctionLike::class], BinaryOp::class => [BinaryOp::class, Expr::class], Expr::class => [Node::class, Expr::class], Stmt::class => [Node::class, Stmt::class], PhpDocTagValueNode::class => [PhpDocTagValueNode::class, \RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node::class], Node::class => [Node::class], RectorInterface::class => [RectorInterface::class]];
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector
     */
    private $genericClassStringTypeCorrector;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(GenericClassStringTypeCorrector $genericClassStringTypeCorrector, ReflectionProvider $reflectionProvider)
    {
        $this->genericClassStringTypeCorrector = $genericClassStringTypeCorrector;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function narrowToSharedObjectType(UnionType $unionType) : ?ObjectType
    {
        $sharedTypes = $this->narrowToSharedTypes($unionType);
        if ($sharedTypes !== []) {
            foreach (self::PRIORITY_TYPES as $winningType => $groupTypes) {
                $intersectedGroupTypes = \array_intersect($groupTypes, $sharedTypes);
                if ($intersectedGroupTypes === $groupTypes) {
                    return new ObjectType($winningType);
                }
            }
            $firstSharedType = $sharedTypes[0];
            return new ObjectType($firstSharedType);
        }
        return null;
    }
    /**
     * @return \PHPStan\Type\UnionType|\PHPStan\Type\Generic\GenericClassStringType
     */
    public function narrowToGenericClassStringType(UnionType $unionType)
    {
        $availableTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof ConstantStringType) {
                $unionedType = $this->genericClassStringTypeCorrector->correct($unionedType);
            }
            if (!$unionedType instanceof GenericClassStringType) {
                return $unionType;
            }
            $genericClassStrings = [];
            if ($unionedType->getGenericType() instanceof ObjectType) {
                $parentClassReflections = $this->resolveClassParentClassesAndInterfaces($unionedType->getGenericType());
                foreach ($parentClassReflections as $parentClassReflection) {
                    $genericClassStrings[] = $parentClassReflection->getName();
                }
            }
            $availableTypes[] = $genericClassStrings;
        }
        $genericClassStringType = $this->createGenericClassStringType($availableTypes);
        if ($genericClassStringType instanceof GenericClassStringType) {
            return $genericClassStringType;
        }
        return $unionType;
    }
    /**
     * @return string[]
     */
    private function narrowToSharedTypes(UnionType $unionType) : array
    {
        $availableTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof ObjectType) {
                return [];
            }
            $typeClassReflections = $this->resolveClassParentClassesAndInterfaces($unionedType);
            $typeClassNames = [];
            foreach ($typeClassReflections as $typeClassReflection) {
                $typeClassNames[] = $typeClassReflection->getName();
            }
            if ($typeClassNames === []) {
                continue;
            }
            $availableTypes[] = $typeClassNames;
        }
        return $this->narrowAvailableTypes($availableTypes);
    }
    /**
     * @return ClassReflection[]
     */
    private function resolveClassParentClassesAndInterfaces(ObjectType $objectType) : array
    {
        if (!$this->reflectionProvider->hasClass($objectType->getClassName())) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        // put earliest interfaces first
        $implementedInterfaceClassReflections = \array_reverse($classReflection->getInterfaces());
        /** @var ClassReflection[] $parentClassAndInterfaceReflections */
        $parentClassAndInterfaceReflections = \array_merge($implementedInterfaceClassReflections, $classReflection->getParents());
        return $this->filterOutNativeClassReflections($parentClassAndInterfaceReflections);
    }
    /**
     * @param string[][] $availableTypes
     * @return string[]
     */
    private function narrowAvailableTypes(array $availableTypes) : array
    {
        if (\count($availableTypes) < 2) {
            return [];
        }
        /** @var string[] $sharedTypes */
        $sharedTypes = \array_intersect(...$availableTypes);
        return \array_values($sharedTypes);
    }
    /**
     * @param string[][] $availableTypes
     */
    private function createGenericClassStringType(array $availableTypes) : ?GenericClassStringType
    {
        $sharedTypes = $this->narrowAvailableTypes($availableTypes);
        if ($sharedTypes !== []) {
            foreach (self::PRIORITY_TYPES as $winningType => $groupTypes) {
                $intersectedGroupTypes = \array_intersect($groupTypes, $sharedTypes);
                if ($intersectedGroupTypes === $groupTypes) {
                    return new GenericClassStringType(new ObjectType($winningType));
                }
            }
            $firstSharedType = $sharedTypes[0];
            return new GenericClassStringType(new ObjectType($firstSharedType));
        }
        return null;
    }
    /**
     * @param ClassReflection[] $classReflections
     * @return ClassReflection[]
     */
    private function filterOutNativeClassReflections(array $classReflections) : array
    {
        return \array_filter($classReflections, function (ClassReflection $classReflection) : bool {
            return !$classReflection->isBuiltin();
        });
    }
}
