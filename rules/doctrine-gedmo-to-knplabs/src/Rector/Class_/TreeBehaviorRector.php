<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeLeftTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeLevelTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeParentTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeRightTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeRootTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeTagValueNode;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/tree.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/tree.md
 *
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\TreeBehaviorRector\TreeBehaviorRectorTest
 */
final class TreeBehaviorRector extends AbstractRector
{
    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    public function __construct(ClassInsertManipulator $classInsertManipulator)
    {
        $this->classInsertManipulator = $classInsertManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change Tree from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
,
                <<<'CODE_SAMPLE'
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

class SomeClass implements TreeNodeInterface
{
    use TreeNodeTrait;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classPhpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($classPhpDocInfo === null) {
            return null;
        }
        $hasTypeTreeTagValueNode = $classPhpDocInfo->hasByType(TreeTagValueNode::class);

        if (! $hasTypeTreeTagValueNode) {
            return null;
        }

        // we're in a tree entity
        $classPhpDocInfo->removeByType(TreeTagValueNode::class);

        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface');
        $this->classInsertManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait');

        // remove all tree-related properties and their getters and setters - it's handled by behavior trait

        $removedPropertyNames = [];

        foreach ($node->getProperties() as $property) {
            $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            if (! $this->shouldRemoveProperty($propertyPhpDocInfo)) {
                continue;
            }

            $removedPropertyNames[] = $this->getName($property);
            $this->removeNode($property);
        }

        $this->removeClassMethodsForProperties($node, $removedPropertyNames);

        return $node;
    }

    private function shouldRemoveProperty(PhpDocInfo $phpDocInfo): bool
    {
        if ($phpDocInfo->hasByType(TreeLeftTagValueNode::class)) {
            return true;
        }

        if ($phpDocInfo->hasByType(TreeRightTagValueNode::class)) {
            return true;
        }

        if ($phpDocInfo->hasByType(TreeRootTagValueNode::class)) {
            return true;
        }

        if ($phpDocInfo->hasByType(TreeParentTagValueNode::class)) {
            return true;
        }
        return $phpDocInfo->hasByType(TreeLevelTagValueNode::class);
    }

    /**
     * @param string[] $removedPropertyNames
     */
    private function removeClassMethodsForProperties(Class_ $class, array $removedPropertyNames): void
    {
        foreach ($removedPropertyNames as $removedPropertyName) {
            foreach ($class->getMethods() as $classMethod) {
                if ($this->isName($classMethod, 'get' . ucfirst($removedPropertyName))) {
                    $this->removeNode($classMethod);
                }

                if ($this->isName($classMethod, 'set' . ucfirst($removedPropertyName))) {
                    $this->removeNode($classMethod);
                }
            }
        }
    }
}
