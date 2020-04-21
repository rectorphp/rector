<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;

final class PropertyToAddCollector implements NodeCollectorInterface
{
    /**
     * @var Type[][]|null[][]
     */
    private $propertiesByClass = [];

    /**
     * @var ClassConst[][]
     */
    private $constantsByClass = [];

    /**
     * @var Type[][]|null[][]
     */
    private $propertiesWithoutConstructorByClass = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isActive(): bool
    {
        if (count($this->propertiesByClass) > 0) {
            return true;
        }

        if (count($this->propertiesWithoutConstructorByClass) > 0) {
            return true;
        }

        return count($this->constantsByClass) > 0;
    }

    public function addPropertyToClass(string $propertyName, ?Type $propertyType, Class_ $class): void
    {
        $this->propertiesByClass[spl_object_hash($class)][$propertyName] = $propertyType;
    }

    public function addConstantToClass(Class_ $class, ClassConst $classConst): void
    {
        $constantName = $this->nodeNameResolver->getName($classConst);
        $this->constantsByClass[spl_object_hash($class)][$constantName] = $classConst;
    }

    public function addPropertyWithoutConstructorToClass(
        string $propertyName,
        ?Type $propertyType,
        Class_ $classNode
    ): void {
        $this->propertiesWithoutConstructorByClass[spl_object_hash($classNode)][$propertyName] = $propertyType;
    }

    /**
     * @var ClassConst[]
     */
    public function getConstantsByClass(Class_ $class): array
    {
        $classHash = spl_object_hash($class);
        return $this->constantsByClass[$classHash] ?? [];
    }

    /**
     * @var Type[]|null[]
     */
    public function getPropertiesByClass(Class_ $class): array
    {
        $classHash = spl_object_hash($class);
        return $this->propertiesByClass[$classHash] ?? [];
    }

    /**
     * @var Type[]|null[]
     */
    public function getPropertiesWithoutConstructorByClass(Class_ $class): array
    {
        $classHash = spl_object_hash($class);
        return $this->propertiesWithoutConstructorByClass[$classHash] ?? [];
    }
}
