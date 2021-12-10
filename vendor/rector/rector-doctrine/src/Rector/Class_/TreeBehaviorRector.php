<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/tree.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/tree.md
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\TreeBehaviorRector\TreeBehaviorRectorTest
 */
final class TreeBehaviorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change Tree from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 */
class SomeClass
{
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @var int
     */
    private $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @var int
     */
    private $rgt;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @var int
     */
    private $lvl;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     * @var Category
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Category
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @var Category[]|Collection
     */
    private $children;

    public function getRoot(): self
    {
        return $this->root;
    }

    public function setParent(self $category): void
    {
        $this->parent = $category;
    }

    public function getParent(): self
    {
        return $this->parent;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

class SomeClass implements TreeNodeInterface
{
    use TreeNodeTrait;
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
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $doctrineAnnotationTagValueNode = $propertyPhpDocInfo->getByAnnotationClass('Gedmo\\Mapping\\Annotation\\Tree');
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        // we're in a tree entity
        $this->phpDocTagRemover->removeTagValueFromNode($propertyPhpDocInfo, $doctrineAnnotationTagValueNode);
        $node->implements[] = new \PhpParser\Node\Name\FullyQualified('Knp\\DoctrineBehaviors\\Contract\\Entity\\TreeNodeInterface');
        $this->classInsertManipulator->addAsFirstTrait($node, 'Knp\\DoctrineBehaviors\\Model\\Tree\\TreeNodeTrait');
        // remove all tree-related properties and their getters and setters - it's handled by behavior trait
        $removedPropertyNames = [];
        foreach ($node->getProperties() as $property) {
            if (!$this->shouldRemoveProperty($property)) {
                continue;
            }
            /** @var string $propertyName */
            $propertyName = $this->getName($property);
            $removedPropertyNames[] = $propertyName;
            $this->removeNode($property);
        }
        $this->removeClassMethodsForProperties($node, $removedPropertyNames);
        return $node;
    }
    private function shouldRemoveProperty(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $phpDocInfo->hasByAnnotationClasses(['Gedmo\\Mapping\\Annotation\\TreeLeft', 'Gedmo\\Mapping\\Annotation\\TreeRight', 'Gedmo\\Mapping\\Annotation\\TreeRoot', 'Gedmo\\Mapping\\Annotation\\TreeParent', 'Gedmo\\Mapping\\Annotation\\TreeLevel']);
    }
    /**
     * @param string[] $removedPropertyNames
     */
    private function removeClassMethodsForProperties(\PhpParser\Node\Stmt\Class_ $class, array $removedPropertyNames) : void
    {
        foreach ($removedPropertyNames as $removedPropertyName) {
            foreach ($class->getMethods() as $classMethod) {
                if ($this->isName($classMethod, 'get' . \ucfirst($removedPropertyName))) {
                    $this->removeNode($classMethod);
                }
                if ($this->isName($classMethod, 'set' . \ucfirst($removedPropertyName))) {
                    $this->removeNode($classMethod);
                }
            }
        }
    }
}
