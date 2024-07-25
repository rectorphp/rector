<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\Enum\MappingClass;
use Rector\Doctrine\Enum\OdmMappingClass;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\Doctrine\NodeFactory\ArrayCollectionAssignFactory;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\ExplicitRelationCollectionRector\ExplicitRelationCollectionRectorTest
 *
 * @changelog https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/best-practices.html#initialize-collections-in-the-constructor
 */
final class ExplicitRelationCollectionRector extends AbstractRector implements MinPhpVersionInterface
{
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
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\ArrayCollectionAssignFactory
     */
    private $arrayCollectionAssignFactory;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    public function __construct(AttrinationFinder $attrinationFinder, ConstructorAssignDetector $constructorAssignDetector, ArrayCollectionAssignFactory $arrayCollectionAssignFactory, ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->attrinationFinder = $attrinationFinder;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->arrayCollectionAssignFactory = $arrayCollectionAssignFactory;
        $this->classDependencyManipulator = $classDependencyManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use Collection object type for one-to-many relations of Doctrine entity/ODM document', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class SomeClass
{
    #[OneToMany(targetEntity: 'SomeClass')]
    private $items = [];
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
    private Collection $items;

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
        if (!$this->attrinationFinder->hasByMany($node, [MappingClass::ENTITY, MappingClass::EMBEDDABLE, OdmMappingClass::DOCUMENT])) {
            return null;
        }
        $arrayCollectionAssigns = [];
        foreach ($node->getProperties() as $property) {
            if (!$this->attrinationFinder->hasByMany($property, [MappingClass::ONE_TO_MANY, MappingClass::MANY_TO_MANY, OdmMappingClass::REFERENCE_MANY])) {
                continue;
            }
            // make sure has collection
            if (!$property->type instanceof Node) {
                $property->type = new FullyQualified('Doctrine\\Common\\Collections\\Collection');
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
            // make sure it is initialized in constructor
        }
        if ($arrayCollectionAssigns === []) {
            return null;
        }
        $this->classDependencyManipulator->addStmtsToConstructorIfNotThereYet($node, $arrayCollectionAssigns);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
