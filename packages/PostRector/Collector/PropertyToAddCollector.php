<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node\Stmt\Class_;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Rector\PostRector\ValueObject\PropertyMetadata;
/**
 * @deprecated Use directly in the class
 */
final class PropertyToAddCollector implements NodeCollectorInterface
{
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    /**
     * @var array<string, PropertyMetadata[]>
     */
    private $propertiesByClass = [];
    public function __construct(RectorChangeCollector $rectorChangeCollector)
    {
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    public function isActive() : bool
    {
        return $this->propertiesByClass !== [];
    }
    public function addPropertyToClass(Class_ $class, PropertyMetadata $propertyMetadata) : void
    {
        $uniqueHash = \spl_object_hash($class);
        $this->propertiesByClass[$uniqueHash][] = $propertyMetadata;
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
}
