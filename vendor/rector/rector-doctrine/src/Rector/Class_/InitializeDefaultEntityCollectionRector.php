<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\Doctrine\NodeFactory\ArrayCollectionAssignFactory;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/best-practices.html#initialize-collections-in-the-constructor
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\InitializeDefaultEntityCollectionRector\InitializeDefaultEntityCollectionRectorTest
 */
final class InitializeDefaultEntityCollectionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var class-string[]
     */
    private const TO_MANY_ANNOTATION_CLASSES = ['Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany'];
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\ArrayCollectionAssignFactory
     */
    private $arrayCollectionAssignFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttrinationFinder
     */
    private $attrinationFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    public function __construct(\Rector\Core\NodeManipulator\ClassDependencyManipulator $classDependencyManipulator, \Rector\Doctrine\NodeFactory\ArrayCollectionAssignFactory $arrayCollectionAssignFactory, \Rector\Doctrine\NodeAnalyzer\AttrinationFinder $attrinationFinder, \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector $constructorAssignDetector)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->arrayCollectionAssignFactory = $arrayCollectionAssignFactory;
        $this->attrinationFinder = $attrinationFinder;
        $this->constructorAssignDetector = $constructorAssignDetector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Initialize collection property in Entity constructor', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];

    public function __construct()
    {
        $this->marketingEvents = new ArrayCollection();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->attrinationFinder->hasByOne($node, 'Doctrine\\ORM\\Mapping\\Entity')) {
            return null;
        }
        return $this->refactorClass($node);
    }
    /**
     * @return string[]
     */
    private function resolveToManyPropertyNames(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $collectionPropertyNames = [];
        foreach ($class->getProperties() as $property) {
            if (\count($property->props) !== 1) {
                continue;
            }
            if (!$this->attrinationFinder->hasByMany($property, self::TO_MANY_ANNOTATION_CLASSES)) {
                continue;
            }
            /** @var string $propertyName */
            $propertyName = $this->getName($property);
            if ($this->constructorAssignDetector->isPropertyAssigned($class, $propertyName)) {
                continue;
            }
            $collectionPropertyNames[] = $propertyName;
        }
        return $collectionPropertyNames;
    }
    /**
     * @param string[] $propertyNames
     * @return Expression[]
     */
    private function createAssignsOfArrayCollectionsForPropertyNames(array $propertyNames) : array
    {
        $assigns = [];
        foreach ($propertyNames as $propertyName) {
            $assigns[] = $this->arrayCollectionAssignFactory->createFromPropertyName($propertyName);
        }
        return $assigns;
    }
    /**
     * @return \PhpParser\Node\Stmt\Class_|null
     */
    private function refactorClass(\PhpParser\Node\Stmt\Class_ $class)
    {
        $toManyPropertyNames = $this->resolveToManyPropertyNames($class);
        if ($toManyPropertyNames === []) {
            return null;
        }
        $assigns = $this->createAssignsOfArrayCollectionsForPropertyNames($toManyPropertyNames);
        $this->classDependencyManipulator->addStmtsToConstructorIfNotThereYet($class, $assigns);
        return $class;
    }
}
