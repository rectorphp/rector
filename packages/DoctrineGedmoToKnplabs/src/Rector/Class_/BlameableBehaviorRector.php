<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\BlameableTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/blameable.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/2cf2585710a9f23d0c8362a7b52f45bf89dc0d3a/docs/blameable.md
 *
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\BlameableBehaviorRector\BlameableBehaviorRectorTest
 */
final class BlameableBehaviorRector extends AbstractRector
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
        return new RectorDefinition('Change Blameable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [
            new CodeSample(
                <<<'PHP'
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @Gedmo\Blameable(on="create")
     */
    private $createdBy;

    /**
     * @Gedmo\Blameable(on="update")
     */
    private $updatedBy;

    /**
     * @Gedmo\Blameable(on="change", field={"title", "body"})
     */
    private $contentChangedBy;

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function getContentChangedBy()
    {
        return $this->contentChangedBy;
    }
}
PHP
,
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;

/**
 * @ORM\Entity
 */
class SomeClass implements BlameableInterface
{
    use BlameableTrait;
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
        if (! $this->isGedmoBlameableClass($node)) {
            return null;
        }

        $this->removeBlameablePropertiesAndMethods($node);

        $this->classManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait');

        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface');

        return $node;
    }

    private function isGedmoBlameableClass(Class_ $class): bool
    {
        foreach ($class->getProperties() as $property) {
            $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            if (! $propertyPhpDocInfo->hasByType(BlameableTagValueNode::class)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function removeBlameablePropertiesAndMethods(Class_ $class): void
    {
        $removedPropertyNames = [];

        foreach ($class->getProperties() as $property) {
            $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            if (! $propertyPhpDocInfo->hasByType(BlameableTagValueNode::class)) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->getName($property);
            $removedPropertyNames[] = $propertyName;

            $this->removeNode($property);
        }

        $this->removeSetterAndGetterByPropertyNames($class, $removedPropertyNames);
    }

    /**
     * @param string[] $removedPropertyNames
     */
    private function removeSetterAndGetterByPropertyNames(Class_ $class, array $removedPropertyNames): void
    {
        foreach ($class->getMethods() as $classMethod) {
            foreach ($removedPropertyNames as $removedPropertyName) {

                // remove methods
                $setMethodName = 'set' . ucfirst($removedPropertyName);
                $getMethodName = 'get' . ucfirst($removedPropertyName);

                if ($this->isNames($classMethod, [$setMethodName, $getMethodName])) {
                    continue;
                }

                $this->removeNode($classMethod);
            }
        }
    }
}
