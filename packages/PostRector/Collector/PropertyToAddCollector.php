<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\Type;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Rector\PostRector\ValueObject\PropertyMetadata;
final class PropertyToAddCollector implements NodeCollectorInterface
{
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
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    public function __construct(RectorChangeCollector $rectorChangeCollector)
    {
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    public function isActive() : bool
    {
        if ($this->propertiesByClass !== []) {
            return \true;
        }
        return $this->propertiesWithoutConstructorByClass !== [];
    }
    public function addPropertyToClass(Class_ $class, PropertyMetadata $propertyMetadata) : void
    {
        $uniqueHash = \spl_object_hash($class);
        $this->propertiesByClass[$uniqueHash][] = $propertyMetadata;
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }
    /**
     * @api
     */
    public function addPropertyWithoutConstructorToClass(string $propertyName, ?Type $propertyType, Class_ $class) : void
    {
        $this->propertiesWithoutConstructorByClass[\spl_object_hash($class)][$propertyName] = $propertyType;
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }
    /**
     * @return PropertyMetadata[]
     */
    public function getPropertiesByClass(Class_ $class) : array
    {
        $classHash = \spl_object_hash($class);
        return $this->propertiesByClass[$classHash] ?? [];
    }
    /**
     * @return array<string, Type|null>
     */
    public function getPropertiesWithoutConstructorByClass(Class_ $class) : array
    {
        $classHash = \spl_object_hash($class);
        return $this->propertiesWithoutConstructorByClass[$classHash] ?? [];
    }
}
