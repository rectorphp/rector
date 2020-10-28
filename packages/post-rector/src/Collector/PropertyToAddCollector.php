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
     * @var ClassConst[][]
     */
    private $constantsByClass = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var Type[][]|null[][]
     */
    private $propertiesByClass = [];

    /**
     * @var Type[][]|null[][]
     */
    private $propertiesWithoutConstructorByClass = [];

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isActive(): bool
    {
        if ($this->propertiesByClass !== []) {
            return true;
        }

        if ($this->propertiesWithoutConstructorByClass !== []) {
            return true;
        }

        return $this->constantsByClass !== [];
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
        Class_ $class
    ): void {
        $this->propertiesWithoutConstructorByClass[spl_object_hash($class)][$propertyName] = $propertyType;
    }

    /**
     * @var ClassConst[]
     * @return ClassConst[]
     */
    public function getConstantsByClass(Class_ $class): array
    {
        $classHash = spl_object_hash($class);
        return $this->constantsByClass[$classHash] ?? [];
    }

    /**
     * @return Type[]|null[]
     */
    public function getPropertiesByClass(Class_ $class): array
    {
        $classHash = spl_object_hash($class);
        return $this->propertiesByClass[$classHash] ?? [];
    }

    /**
     * @return Type[]|null[]
     */
    public function getPropertiesWithoutConstructorByClass(Class_ $class): array
    {
        $classHash = spl_object_hash($class);
        return $this->propertiesWithoutConstructorByClass[$classHash] ?? [];
    }
}
