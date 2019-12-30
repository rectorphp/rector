<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\SluggableBehaviorRector\SluggableBehaviorRectorTest
 */
final class SluggableBehaviorRector extends AbstractRector
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
        return new RectorDefinition('Change Sluggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [
            new CodeSample(
                <<<'PHP'
use Gedmo\Mapping\Annotation as Gedmo;

class SomeClass
{
    /**
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
PHP
,
                <<<'PHP'
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

class SomeClass implements SluggableInterface
{
    use SluggableTrait;

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['name'];
    }
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
        $slugFields = [];
        $matchedProperty = null;

        foreach ($node->getProperties() as $property) {
            $propertyPhpDocInfo = $this->getPhpDocInfo($property);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            $slugTagValueNode = $propertyPhpDocInfo->getByType(SlugTagValueNode::class);
            if ($slugTagValueNode === null) {
                continue;
            }

            $slugFields = $slugTagValueNode->getFields();
            $this->removeNode($property);

            $matchedProperty = $property;
        }

        if ($matchedProperty === null) {
            return null;
        }

        // remove property setter/getter

        foreach ((array) $node->getMethods() as $classMethod) {
            if (! $this->isNames($classMethod, ['getSlug', 'setSlug'])) {
                continue;
            }

            $this->removeNode($classMethod);
        }

        $this->classManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait');

        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface');

        $this->addGetSluggableFieldsClassMethod($node, $slugFields);

        // change the node

        return $node;
    }

    /**
     * @param string[] $slugFields
     */
    private function addGetSluggableFieldsClassMethod(Class_ $class, array $slugFields): void
    {
        $classMethod = $this->nodeFactory->createPublicMethod('getSluggableFields');
        $classMethod->returnType = new Identifier('array');
        $classMethod->stmts[] = new Return_($this->createArray($slugFields));

        $this->docBlockManipulator->addReturnTag($classMethod, new ArrayType(new MixedType(), new StringType()));

        $this->classManipulator->addAsFirstMethod($class, $classMethod);
    }
}
