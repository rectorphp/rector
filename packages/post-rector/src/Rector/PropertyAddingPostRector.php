<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\PostRector\NodeAnalyzer\NetteInjectDetector;

/**
 * Adds new private properties to class + to constructor
 */
final class PropertyAddingPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
    /**
     * @var ClassDependencyManipulator
     */
    private $classDependencyManipulator;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    /**
     * @var PropertyToAddCollector
     */
    private $propertyToAddCollector;

    /**
     * @var NetteInjectDetector
     */
    private $netteInjectDetector;

    public function __construct(
        ClassDependencyManipulator $classDependencyManipulator,
        ClassInsertManipulator $classInsertManipulator,
        NetteInjectDetector $netteInjectDetector,
        PropertyToAddCollector $propertyToAddCollector
    ) {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->netteInjectDetector = $netteInjectDetector;
    }

    public function getPriority(): int
    {
        return 900;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof Class_) {
            return null;
        }
        if ($node->isAnonymous()) {
            return null;
        }
        $this->addConstants($node);
        $this->addProperties($node);
        $this->addPropertiesWithoutConstructor($node);

        return $node;
    }

    private function addConstants(Class_ $class): void
    {
        $constants = $this->propertyToAddCollector->getConstantsByClass($class);

        foreach ($constants as $constantName => $nodeConst) {
            $this->classInsertManipulator->addConstantToClass($class, $constantName, $nodeConst);
        }
    }

    private function addProperties(Class_ $class): void
    {
        $propertiesMetadatas = $this->propertyToAddCollector->getPropertiesByClass($class);

        $isNetteInjectPreferred = $this->netteInjectDetector->isNetteInjectPreferred($class);

        foreach ($propertiesMetadatas as $propertyMetadata) {
            if (! $isNetteInjectPreferred) {
                $this->classDependencyManipulator->addConstructorDependency($class, $propertyMetadata);
            } else {
                $this->classDependencyManipulator->addInjectProperty($class, $propertyMetadata);
            }
        }
    }

    private function addPropertiesWithoutConstructor(Class_ $class): void
    {
        $propertiesWithoutConstructor = $this->propertyToAddCollector->getPropertiesWithoutConstructorByClass(
            $class
        );

        foreach ($propertiesWithoutConstructor as $propertyName => $propertyType) {
            $this->classInsertManipulator->addPropertyToClass($class, $propertyName, $propertyType);
        }
    }
}
