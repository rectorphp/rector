<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SoftDeleteableTagValueNode;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\SoftDeletableBehaviorRector\SoftDeletableBehaviorRectorTest
 */
final class SoftDeletableBehaviorRector extends AbstractRector
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
        return new RectorDefinition(
            'Change SoftDeletable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors',
            [
                new CodeSample(
                    <<<'PHP'
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class SomeClass
{
    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }
}
PHP
,
                    <<<'PHP'
use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;

class SomeClass implements SoftDeletableInterface
{
    use SoftDeletableTrait;
}
PHP

                ),
            ]
        );
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
        // Gedmo\Mapping\Annotation\SoftDeleteable
        $classPhpDocInfo = $this->getPhpDocInfo($node);
        if ($classPhpDocInfo === null) {
            return null;
        }

        if (! $classPhpDocInfo->hasByType(SoftDeleteableTagValueNode::class)) {
            return null;
        }

        /** @var SoftDeleteableTagValueNode $softDeleteableTagValueNode */
        $softDeleteableTagValueNode = $classPhpDocInfo->getByType(SoftDeleteableTagValueNode::class);
        $fieldName = $softDeleteableTagValueNode->getFieldName();
        $this->removePropertyAndClassMethods($node, $fieldName);

        $this->classManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait');

        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface');

        $classPhpDocInfo->removeByType(SoftDeleteableTagValueNode::class);
        $this->docBlockManipulator->updateNodeWithPhpDocInfo($node, $classPhpDocInfo);

        return $node;
    }

    private function removePropertyAndClassMethods(Class_ $class, string $fieldName): void
    {
        // remove property
        foreach ($class->getProperties() as $property) {
            if (! $this->isName($property, $fieldName)) {
                continue;
            }

            $this->removeNode($property);
        }

        // remove methods
        $setMethodName = 'set' . ucfirst($fieldName);
        $getMethodName = 'get' . ucfirst($fieldName);

        foreach ($class->getMethods() as $classMethod) {
            if (! $this->isNames($classMethod, [$setMethodName, $getMethodName])) {
                continue;
            }

            $this->removeNode($classMethod);
        }
    }
}
