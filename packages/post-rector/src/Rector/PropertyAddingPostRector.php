<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Manipulator\ClassDependencyManipulator;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\NodeAnalyzer\NetteInjectDetector;

/**
 * Adds new private properties to class + to constructor
 */
final class PropertyAddingPostRector extends AbstractPostRector
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
        if (! $node instanceof Class_ || $node->isAnonymous()) {
            return null;
        }

        $this->addConstants($node);
        $this->addProperties($node);
        $this->addPropertiesWithoutConstructor($node);

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Post Rector that adds properties');
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
        $properties = $this->propertyToAddCollector->getPropertiesByClass($class);

        $isNetteInjectPreferred = $this->netteInjectDetector->isNetteInjectPreferred($class);

        foreach ($properties as $propertyName => $propertyType) {
            if (! $isNetteInjectPreferred) {
                $this->classDependencyManipulator->addConstructorDependency($class, $propertyName, $propertyType);
            } else {
                $this->classDependencyManipulator->addInjectProperty($class, $propertyName, $propertyType);
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
