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
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/sluggable.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/sluggable.md
 *
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\SluggableBehaviorRector\SluggableBehaviorRectorTest
 */
final class SluggableBehaviorRector extends AbstractRector
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
        return new RectorDefinition('Change Sluggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
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
        $slugFields = [];
        $matchedProperty = null;

        foreach ($node->getProperties() as $property) {
            $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            /** @var SlugTagValueNode|null $slugTagValueNode */
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

        $this->classInsertManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait');

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

        $returnType = new ArrayType(new MixedType(), new StringType());
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        $phpDocInfo->changeReturnType($returnType);
//        $this->docBlockManipulator->addReturnTag($classMethod, new ArrayType(new MixedType(), new StringType()));

        $this->classInsertManipulator->addAsFirstMethod($class, $classMethod);
    }
}
