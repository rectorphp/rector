<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\NodeAnalyzer\NetteInjectDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Adds new private properties to class + to constructor
 */
final class PropertyAddingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\PostRector\NodeAnalyzer\NetteInjectDetector
     */
    private $netteInjectDetector;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\ClassDependencyManipulator $classDependencyManipulator, \Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\PostRector\NodeAnalyzer\NetteInjectDetector $netteInjectDetector, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->netteInjectDetector = $netteInjectDetector;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->classAnalyzer = $classAnalyzer;
    }
    public function getPriority() : int
    {
        return 900;
    }
    public function enterNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $this->addConstants($node);
        $this->addProperties($node);
        $this->addPropertiesWithoutConstructor($node);
        return $node;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add dependency properties', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return $this->value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $value;
    public function run()
    {
        return $this->value;
    }
}
CODE_SAMPLE
)]);
    }
    private function addConstants(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $constants = $this->propertyToAddCollector->getConstantsByClass($class);
        foreach ($constants as $constantName => $nodeConst) {
            $this->classInsertManipulator->addConstantToClass($class, $constantName, $nodeConst);
        }
    }
    private function addProperties(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $propertiesMetadatas = $this->propertyToAddCollector->getPropertiesByClass($class);
        $isNetteInjectPreferred = $this->netteInjectDetector->isNetteInjectPreferred($class);
        foreach ($propertiesMetadatas as $propertyMetadata) {
            if (!$isNetteInjectPreferred) {
                $this->classDependencyManipulator->addConstructorDependency($class, $propertyMetadata);
            } else {
                $this->classDependencyManipulator->addInjectProperty($class, $propertyMetadata);
            }
        }
    }
    private function addPropertiesWithoutConstructor(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $propertiesWithoutConstructor = $this->propertyToAddCollector->getPropertiesWithoutConstructorByClass($class);
        foreach ($propertiesWithoutConstructor as $propertyName => $propertyType) {
            $this->classInsertManipulator->addPropertyToClass($class, $propertyName, $propertyType);
        }
    }
}
