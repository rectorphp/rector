<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\NodeFactory\ArrayCollectionAssignFactory;
use Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Class_\InitializeCollectionInConstructorRector\InitializeCollectionInConstructorRectorTest
 *
 * @changelog https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/best-practices.html#initialize-collections-in-the-constructor
 */
final class InitializeCollectionInConstructorRector extends AbstractRector
{
    /**
     * @readonly
     */
    private EntityLikeClassDetector $entityLikeClassDetector;
    /**
     * @readonly
     */
    private ConstructorAssignDetector $constructorAssignDetector;
    /**
     * @readonly
     */
    private ArrayCollectionAssignFactory $arrayCollectionAssignFactory;
    /**
     * @readonly
     */
    private ClassDependencyManipulator $classDependencyManipulator;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(EntityLikeClassDetector $entityLikeClassDetector, ConstructorAssignDetector $constructorAssignDetector, ArrayCollectionAssignFactory $arrayCollectionAssignFactory, ClassDependencyManipulator $classDependencyManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->entityLikeClassDetector = $entityLikeClassDetector;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->arrayCollectionAssignFactory = $arrayCollectionAssignFactory;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Initialize Collection property in entity/ODM __construct()', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class SomeClass
{
    #[OneToMany(targetEntity: 'SomeClass')]
    private $items;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[Entity]
class SomeClass
{
    #[OneToMany(targetEntity: 'SomeClass')]
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->entityLikeClassDetector->detect($node)) {
            return null;
        }
        if ($this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node->isAbstract()) {
            return null;
        }
        $arrayCollectionAssigns = [];
        foreach ($node->getProperties() as $property) {
            if (!$this->entityLikeClassDetector->isToMany($property)) {
                continue;
            }
            // make sure is null
            if ($property->props[0]->default instanceof Expr) {
                $property->props[0]->default = null;
            }
            /** @var string $propertyName */
            $propertyName = $this->getName($property);
            if ($this->constructorAssignDetector->isPropertyAssigned($node, $propertyName)) {
                continue;
            }
            $arrayCollectionAssigns[] = $this->arrayCollectionAssignFactory->createFromPropertyName($propertyName);
        }
        if ($arrayCollectionAssigns === []) {
            return null;
        }
        $this->classDependencyManipulator->addStmtsToConstructorIfNotThereYet($node, $arrayCollectionAssigns);
        return $node;
    }
}
