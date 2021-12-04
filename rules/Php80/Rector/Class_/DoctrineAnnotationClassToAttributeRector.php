<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeAnalyzer\AnnotationTargetResolver;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php80\NodeFactory\AttributeFlagFactory;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @changelog https://php.watch/articles/php-attributes#syntax
 *
 * @see https://github.com/doctrine/annotations/blob/1.13.x/lib/Doctrine/Common/Annotations/Annotation/Target.php
 * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/docs/en/custom.rst#annotation-required
 *
 * @see \Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\DoctrineAnnotationClassToAttributeRectorTest
 */
final class DoctrineAnnotationClassToAttributeRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var string
     */
    final public const REMOVE_ANNOTATIONS = 'remove_annotations';

    /**
     * @var string
     */
    private const ATTRIBUTE = 'Attribute';

    private bool $shouldRemoveAnnotations = true;

    public function __construct(
        private readonly PhpDocTagRemover $phpDocTagRemover,
        private readonly AttributeFlagFactory $attributeFlagFactory,
        private readonly PhpAttributeGroupFactory $phpAttributeGroupFactory,
        private readonly PhpAttributeAnalyzer $phpAttributeAnalyzer,
        private readonly PropertyToAddCollector $propertyToAddCollector,
        private readonly AnnotationTargetResolver $annotationTargetResolver
    ) {
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor Doctrine @annotation annotated class to a PHP 8.0 attribute class', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class SomeAnnotation
{
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SomeAnnotation
{
}
CODE_SAMPLE
                ,
                [
                    self::REMOVE_ANNOTATIONS => true,
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        if ($this->shouldSkipClass($phpDocInfo, $node)) {
            return null;
        }

        if ($this->shouldRemoveAnnotations) {
            $this->phpDocTagRemover->removeByName($phpDocInfo, 'annotation');
            $this->phpDocTagRemover->removeByName($phpDocInfo, 'Annotation');
        }

        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(self::ATTRIBUTE);
        $this->decorateTarget($phpDocInfo, $attributeGroup);

        foreach ($node->getProperties() as $property) {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            if (! $propertyPhpDocInfo instanceof PhpDocInfo) {
                continue;
            }

            $requiredDoctrineAnnotationTagValueNode = $propertyPhpDocInfo->findOneByAnnotationClass(
                'Doctrine\Common\Annotations\Annotation\Required'
            );
            if (! $requiredDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }

            if ($this->shouldRemoveAnnotations) {
                $this->phpDocTagRemover->removeTagValueFromNode(
                    $propertyPhpDocInfo,
                    $requiredDoctrineAnnotationTagValueNode
                );
            }

            // require in constructor
            $propertyName = $this->getName($property);

            $propertyMetadata = new PropertyMetadata($propertyName, new MixedType(), Class_::MODIFIER_PUBLIC);
            $this->propertyToAddCollector->addPropertyToClass($node, $propertyMetadata);

            if ($this->shouldRemoveAnnotations) {
                $this->removeNode($property);
            }
        }

        $node->attrGroups[] = $attributeGroup;

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $shouldRemoveAnnotations = $configuration[self::REMOVE_ANNOTATIONS] ?? true;
        Assert::boolean($shouldRemoveAnnotations);
        $this->shouldRemoveAnnotations = $shouldRemoveAnnotations;
    }

    private function decorateTarget(PhpDocInfo $phpDocInfo, AttributeGroup $attributeGroup): void
    {
        $targetDoctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClasses([
            'Doctrine\Common\Annotations\Annotation\Target',
            'Target',
        ]);

        if (! $targetDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }

        if ($this->shouldRemoveAnnotations) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $targetDoctrineAnnotationTagValueNode);
        }

        $targets = $targetDoctrineAnnotationTagValueNode->getSilentValue();

        if ($targets instanceof CurlyListNode) {
            $targetValues = $targets->getValuesWithExplicitSilentAndWithoutQuotes();
        } elseif (is_string($targets)) {
            $targetValues = [$targets];
        } else {
            return;
        }

        $flagClassConstFetches = $this->annotationTargetResolver->resolveFlagClassConstFetches($targetValues);

        $flagCollection = $this->attributeFlagFactory->createFlagCollection($flagClassConstFetches);
        if ($flagCollection === null) {
            return;
        }

        $attributeGroup->attrs[0]->args[] = new Arg($flagCollection);
    }

    private function shouldSkipClass(PhpDocInfo $phpDocInfo, Class_ $class): bool
    {
        if (! $phpDocInfo->hasByNames(['Annotation', 'annotation'])) {
            return true;
        }

        // has attribute? skip it
        return $this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE);
    }
}
