<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\LoggableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\VersionedTagValueNode;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/loggable.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/loggable.md
 *
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\LoggableBehaviorRector\LoggableBehaviorRectorTest
 */
final class LoggableBehaviorRector extends AbstractRector
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
        return new RectorDefinition('Change Loggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
        // change the node
        $classPhpDocInfo = $this->getPhpDocInfo($node);
        if ($classPhpDocInfo === null) {
            return null;
        }

        if (! $classPhpDocInfo->hasByType(LoggableTagValueNode::class)) {
            return null;
        }

        $classPhpDocInfo->removeByType(LoggableTagValueNode::class);

        // remove tag from properties
        $this->removeVersionedTagFromProperties($node);

        $this->classManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait');

        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface');

        return $node;
    }

    private function removeVersionedTagFromProperties(Class_ $class): void
    {
        foreach ($class->getProperties() as $property) {
            $propertyPhpDocInfo = $this->getPhpDocInfo($property);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            if (! $propertyPhpDocInfo->hasByType(VersionedTagValueNode::class)) {
                continue;
            }

            $propertyPhpDocInfo->removeByType(VersionedTagValueNode::class);
        }
    }
}
