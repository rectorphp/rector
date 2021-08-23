<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector;
final class UnionTypeCommonTypeNarrower
{
    /**
     * Key = the winner Array = the group of types matched
     *
     * @var array<string, array<class-string<Node>|class-string<\PHPStan\PhpDocParser\Ast\Node>|class-string<RectorInterface>>>
     */
    private const PRIORITY_TYPES = [\PhpParser\Node\Stmt\ClassLike::class => [\PhpParser\Node\Stmt\ClassLike::class], \PhpParser\Node\FunctionLike::class => [\PhpParser\Node\FunctionLike::class], \PhpParser\Node\Expr\BinaryOp::class => [\PhpParser\Node\Expr\BinaryOp::class, \PhpParser\Node\Expr::class], \PhpParser\Node\Expr::class => [\PhpParser\Node::class, \PhpParser\Node\Expr::class], \PhpParser\Node\Stmt::class => [\PhpParser\Node::class, \PhpParser\Node\Stmt::class], \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode::class => [\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode::class, \PHPStan\PhpDocParser\Ast\Node::class], \PhpParser\Node::class => [\PhpParser\Node::class], \Rector\Core\Contract\Rector\RectorInterface::class => [\Rector\Core\Contract\Rector\RectorInterface::class]];
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector
     */
    private $genericClassStringTypeCorrector;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector $genericClassStringTypeCorrector, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->genericClassStringTypeCorrector = $genericClassStringTypeCorrector;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function narrowToSharedObjectType(\PHPStan\Type\UnionType $unionType) : ?\PHPStan\Type\ObjectType
    {
        $sharedTypes = $this->narrowToSharedTypes($unionType);
        if ($sharedTypes !== []) {
            foreach (self::PRIORITY_TYPES as $winningType => $groupTypes) {
                $intersectedGroupTypes = \array_intersect($groupTypes, $sharedTypes);
                if ($intersectedGroupTypes === $groupTypes) {
                    return new \PHPStan\Type\ObjectType($winningType);
                }
            }
            $firstSharedType = $sharedTypes[0];
            return new \PHPStan\Type\ObjectType($firstSharedType);
        }
        return null;
    }
    /**
     * @return \PHPStan\Type\UnionType|\PHPStan\Type\Generic\GenericClassStringType
     */
    public function narrowToGenericClassStringType(\PHPStan\Type\UnionType $unionType)
    {
        $availableTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                $unionedType = $this->genericClassStringTypeCorrector->correct($unionedType);
            }
            if (!$unionedType instanceof \PHPStan\Type\Generic\GenericClassStringType) {
                return $unionType;
            }
            $genericClassStrings = [];
            if ($unionedType->getGenericType() instanceof \PHPStan\Type\ObjectType) {
                $parentClassReflections = $this->resolveClassParentClassesAndInterfaces($unionedType->getGenericType());
                foreach ($parentClassReflections as $parentClassReflection) {
                    $genericClassStrings[] = $parentClassReflection->getName();
                }
            }
            $availableTypes[] = $genericClassStrings;
        }
        $genericClassStringType = $this->createGenericClassStringType($availableTypes);
        if ($genericClassStringType instanceof \PHPStan\Type\Generic\GenericClassStringType) {
            return $genericClassStringType;
        }
        return $unionType;
    }
    /**
     * @return string[]
     */
    private function narrowToSharedTypes(\PHPStan\Type\UnionType $unionType) : array
    {
        $availableTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof \PHPStan\Type\ObjectType) {
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
    private function resolveClassParentClassesAndInterfaces(\PHPStan\Type\ObjectType $objectType) : array
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
    private function createGenericClassStringType(array $availableTypes) : ?\PHPStan\Type\Generic\GenericClassStringType
    {
        $sharedTypes = $this->narrowAvailableTypes($availableTypes);
        if ($sharedTypes !== []) {
            foreach (self::PRIORITY_TYPES as $winningType => $groupTypes) {
                $intersectedGroupTypes = \array_intersect($groupTypes, $sharedTypes);
                if ($intersectedGroupTypes === $groupTypes) {
                    return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($winningType));
                }
            }
            $firstSharedType = $sharedTypes[0];
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($firstSharedType));
        }
        return null;
    }
    /**
     * @param ClassReflection[] $classReflections
     * @return ClassReflection[]
     */
    private function filterOutNativeClassReflections(array $classReflections) : array
    {
        return \array_filter($classReflections, function (\PHPStan\Reflection\ClassReflection $classReflection) : bool {
            return !$classReflection->isBuiltin();
        });
    }
}
