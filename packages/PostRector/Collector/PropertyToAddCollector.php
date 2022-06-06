<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Type\Type;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Rector\PostRector\ValueObject\PropertyMetadata;
final class PropertyToAddCollector implements \Rector\PostRector\Contract\Collector\NodeCollectorInterface
{
    /**
     * @var array<string, array<string, ClassConst>>
     */
    private $constantsByClass = [];
    /**
     * @var array<string, PropertyMetadata[]>
     */
    private $propertiesByClass = [];
    /**
     * @var array<string, array<string, Type|null>>
     */
    private $propertiesWithoutConstructorByClass = [];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    public function isActive() : bool
    {
        if ($this->propertiesByClass !== []) {
            return \true;
        }
        if ($this->propertiesWithoutConstructorByClass !== []) {
            return \true;
        }
        return $this->constantsByClass !== [];
    }
    public function addPropertyToClass(\PhpParser\Node\Stmt\Class_ $class, \Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : void
    {
        $uniqueHash = \spl_object_hash($class);
        $this->propertiesByClass[$uniqueHash][] = $propertyMetadata;
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }
    public function addConstantToClass(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassConst $classConst) : void
    {
        $constantName = $this->nodeNameResolver->getName($classConst);
        $this->constantsByClass[\spl_object_hash($class)][$constantName] = $classConst;
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }
    public function addPropertyWithoutConstructorToClass(string $propertyName, ?\PHPStan\Type\Type $propertyType, \PhpParser\Node\Stmt\Class_ $class) : void
    {
        $this->propertiesWithoutConstructorByClass[\spl_object_hash($class)][$propertyName] = $propertyType;
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }
    /**
     * @return ClassConst[]
     */
    public function getConstantsByClass(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $classHash = \spl_object_hash($class);
        return $this->constantsByClass[$classHash] ?? [];
    }
    /**
     * @return PropertyMetadata[]
     */
    public function getPropertiesByClass(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $classHash = \spl_object_hash($class);
        return $this->propertiesByClass[$classHash] ?? [];
    }
    /**
     * @return array<string, Type|null>
     */
    public function getPropertiesWithoutConstructorByClass(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $classHash = \spl_object_hash($class);
        return $this->propertiesWithoutConstructorByClass[$classHash] ?? [];
    }
}
