<?php

declare (strict_types=1);
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
use RectorPrefix20211231\Webmozart\Assert\Assert;
/**
 * @changelog https://php.watch/articles/php-attributes#syntax
 *
 * @see https://github.com/doctrine/annotations/blob/1.13.x/lib/Doctrine/Common/Annotations/Annotation/Target.php
 * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/docs/en/custom.rst#annotation-required
 *
 * @see \Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\DoctrineAnnotationClassToAttributeRectorTest
 */
final class DoctrineAnnotationClassToAttributeRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface, \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const REMOVE_ANNOTATIONS = 'remove_annotations';
    /**
     * @var string
     */
    private const ATTRIBUTE = 'Attribute';
    /**
     * @var bool
     */
    private $shouldRemoveAnnotations = \true;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\AttributeFlagFactory
     */
    private $attributeFlagFactory;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\AnnotationTargetResolver
     */
    private $annotationTargetResolver;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\Php80\NodeFactory\AttributeFlagFactory $attributeFlagFactory, \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory $phpAttributeGroupFactory, \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer $phpAttributeAnalyzer, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Php80\NodeAnalyzer\AnnotationTargetResolver $annotationTargetResolver)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->attributeFlagFactory = $attributeFlagFactory;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->annotationTargetResolver = $annotationTargetResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor Doctrine @annotation annotated class to a PHP 8.0 attribute class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class SomeAnnotation
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SomeAnnotation
{
}
CODE_SAMPLE
, [self::REMOVE_ANNOTATIONS => \true])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
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
            if (!$propertyPhpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                continue;
            }
            $requiredDoctrineAnnotationTagValueNode = $propertyPhpDocInfo->findOneByAnnotationClass('Doctrine\\Common\\Annotations\\Annotation\\Required');
            if (!$requiredDoctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                continue;
            }
            if ($this->shouldRemoveAnnotations) {
                $this->phpDocTagRemover->removeTagValueFromNode($propertyPhpDocInfo, $requiredDoctrineAnnotationTagValueNode);
            }
            // require in constructor
            $propertyName = $this->getName($property);
            $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, new \PHPStan\Type\MixedType(), \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC);
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
    public function configure(array $configuration) : void
    {
        $shouldRemoveAnnotations = $configuration[self::REMOVE_ANNOTATIONS] ?? \true;
        \RectorPrefix20211231\Webmozart\Assert\Assert::boolean($shouldRemoveAnnotations);
        $this->shouldRemoveAnnotations = $shouldRemoveAnnotations;
    }
    private function decorateTarget(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\AttributeGroup $attributeGroup) : void
    {
        $targetDoctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClasses(['Doctrine\\Common\\Annotations\\Annotation\\Target', 'Target']);
        if (!$targetDoctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return;
        }
        if ($this->shouldRemoveAnnotations) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $targetDoctrineAnnotationTagValueNode);
        }
        $targets = $targetDoctrineAnnotationTagValueNode->getSilentValue();
        if ($targets instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            $targetValues = $targets->getValuesWithExplicitSilentAndWithoutQuotes();
        } elseif (\is_string($targets)) {
            $targetValues = [$targets];
        } else {
            return;
        }
        $flagClassConstFetches = $this->annotationTargetResolver->resolveFlagClassConstFetches($targetValues);
        $flagCollection = $this->attributeFlagFactory->createFlagCollection($flagClassConstFetches);
        if ($flagCollection === null) {
            return;
        }
        $attributeGroup->attrs[0]->args[] = new \PhpParser\Node\Arg($flagCollection);
    }
    private function shouldSkipClass(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\Class_ $class) : bool
    {
        if (!$phpDocInfo->hasByNames(['Annotation', 'annotation'])) {
            return \true;
        }
        // has attribute? skip it
        return $this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE);
    }
}
