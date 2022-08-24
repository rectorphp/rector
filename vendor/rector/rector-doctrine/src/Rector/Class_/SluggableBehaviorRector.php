<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeFactory\SluggableClassMethodFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/sluggable.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/sluggable.md
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\SluggableBehaviorRector\SluggableBehaviorRectorTest
 */
final class SluggableBehaviorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\SluggableClassMethodFactory
     */
    private $sluggableClassMethodFactory;
    public function __construct(ClassInsertManipulator $classInsertManipulator, SluggableClassMethodFactory $sluggableClassMethodFactory)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->sluggableClassMethodFactory = $sluggableClassMethodFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change Sluggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
        $slugFieldsArrayItemNode = [];
        $matchedProperty = null;
        foreach ($node->getProperties() as $property) {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            $doctrineAnnotationTagValueNode = $propertyPhpDocInfo->getByAnnotationClass('Gedmo\\Mapping\\Annotation\\Slug');
            if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $slugFieldsArrayItemNode = $doctrineAnnotationTagValueNode->getValue('fields');
            $this->removeNode($property);
            $matchedProperty = $property;
        }
        if ($matchedProperty === null) {
            return null;
        }
        // remove property setter/getter
        foreach ($node->getMethods() as $classMethod) {
            if (!$this->isNames($classMethod, ['getSlug', 'setSlug'])) {
                continue;
            }
            $this->removeNode($classMethod);
        }
        $this->classInsertManipulator->addAsFirstTrait($node, 'Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableTrait');
        $node->implements[] = new FullyQualified('Knp\\DoctrineBehaviors\\Contract\\Entity\\SluggableInterface');
        if (!$slugFieldsArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        $getSluggableClassMethod = $this->sluggableClassMethodFactory->createGetSluggableFields($slugFieldsArrayItemNode);
        $this->classInsertManipulator->addAsFirstMethod($node, $getSluggableClassMethod);
        return $node;
    }
}
