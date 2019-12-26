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
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\TreeBehaviorRector\TreeBehaviorRectorTest
 */
final class TreeBehaviorRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change Tree from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

class SomeClass implements TreeNodeInterface
{
    use TreeNodeTrait;
}
PHP

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
        $classPhpDocInfo = $this->getPhpDocInfo($node);
        if ($classPhpDocInfo === null) {
            return null;
        }

        $treeTagValueNode = $classPhpDocInfo->getByType(TreeTagValueNode::class);
        if ($treeTagValueNode === null) {
            return null;
        }

        // we're in a tree entity
        $classPhpDocInfo->removeTagValueNodeFromNode($treeTagValueNode);
        $this->docBlockManipulator->updateNodeWithPhpDocInfo($node, $classPhpDocInfo);

        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface');
        $this->classManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait');

        // remove all tree-related properties and their getters and setters - it's handled by behavior trait

        $removedPropertyNames = [];

        foreach ($node->getProperties() as $property) {
            $propertyPhpDocInfo = $this->getPhpDocInfo($property);
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

    private function removeClassMethodsForProperties(Class_ $class, array $removedPropertyNames): void
    {
        foreach ($removedPropertyNames as $removedPropertyName) {
            foreach ($class->getMethods() as $method) {
                if ($this->isName($method, 'get' . ucfirst($removedPropertyName))) {
                    $this->removeNode($method);
                }

                if ($this->isName($method, 'set' . ucfirst($removedPropertyName))) {
                    $this->removeNode($method);
                }
            }
        }
    }

    private function shouldRemoveProperty(PhpDocInfo $phpDocInfo): bool
    {
        if ($phpDocInfo->getByType(TreeLeftTagValueNode::class)) {
            return true;
        }

        if ($phpDocInfo->getByType(TreeRightTagValueNode::class)) {
            return true;
        }

        if ($phpDocInfo->getByType(TreeRootTagValueNode::class)) {
            return true;
        }

        if ($phpDocInfo->getByType(TreeParentTagValueNode::class)) {
            return true;
        }

        if ($phpDocInfo->getByType(TreeLevelTagValueNode::class)) {
            return true;
        }

        return false;
    }
}
