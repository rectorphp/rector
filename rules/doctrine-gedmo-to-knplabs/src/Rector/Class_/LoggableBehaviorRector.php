<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\LoggableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\VersionedTagValueNode;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/loggable.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/loggable.md
 *
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\LoggableBehaviorRector\LoggableBehaviorRectorTest
 */
final class LoggableBehaviorRector extends AbstractRector
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
        return new RectorDefinition('Change Loggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class SomeClass
{
    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="title", type="string", length=8)
     */
    private $title;
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;

/**
 * @ORM\Entity
 */
class SomeClass implements LoggableInterface
{
    use LoggableTrait;

    /**
     * @ORM\Column(name="title", type="string", length=8)
     */
    private $title;
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
        // change the node
        $classPhpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($classPhpDocInfo === null) {
            return null;
        }
        $hasTypeLoggableTagValueNode = $classPhpDocInfo->hasByType(LoggableTagValueNode::class);

        if (! $hasTypeLoggableTagValueNode) {
            return null;
        }

        $classPhpDocInfo->removeByType(LoggableTagValueNode::class);

        // remove tag from properties
        $this->removeVersionedTagFromProperties($node);

        $this->classInsertManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait');

        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface');

        return $node;
    }

    private function removeVersionedTagFromProperties(Class_ $class): void
    {
        foreach ($class->getProperties() as $property) {
            $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($propertyPhpDocInfo === null) {
                continue;
            }
            $hasTypeVersionedTagValueNode = $propertyPhpDocInfo->hasByType(VersionedTagValueNode::class);

            if (! $hasTypeVersionedTagValueNode) {
                continue;
            }

            $propertyPhpDocInfo->removeByType(VersionedTagValueNode::class);
        }
    }
}
