<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Node as DocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\Annotation\InverseJoinColumnCorrector;
use Rector\BetterPhpDocParser\AnnotationAnalyzer\DoctrineAnnotationTagValueNodeAnalyzer;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeFactory\AttrGroupsFactory;
use Rector\Php80\PhpDocCleaner\ConvertedAnnotationToAttributeParentRemover;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use Webmozart\Assert\Assert;

/**
 * @changelog https://wiki.php.net/rfc/attributes_v2
 *
 * @see \Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\AnnotationToAttributeRectorTest
 */
final class AnnotationToAttributeRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var string
     */
    public const ANNOTATION_TO_ATTRIBUTE = 'annotations_to_attributes';

    /**
     * List of annotations that should not be unwrapped
     * @var string[]
     */
    private const SKIP_UNWRAP_ANNOTATIONS = [
        'Symfony\Component\Validator\Constraints\All',
        'Symfony\Component\Validator\Constraints\AtLeastOneOf',
        'Symfony\Component\Validator\Constraints\Collection',
        'Symfony\Component\Validator\Constraints\Sequentially',
    ];

    /**
     * @var AnnotationToAttribute[]
     */
    private array $annotationsToAttributes = [];

    public function __construct(
        private PhpAttributeGroupFactory $phpAttributeGroupFactory,
        private ConvertedAnnotationToAttributeParentRemover $convertedAnnotationToAttributeParentRemover,
        private AttrGroupsFactory $attrGroupsFactory,
        private DoctrineAnnotationTagValueNodeAnalyzer $doctrineAnnotationTagValueNodeAnalyzer,
        private InverseJoinColumnCorrector $inverseJoinColumnCorrector,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change annotation to attribute', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    /**
     * @Route("/path", name="action")
     */
    public function action()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    #[Route(path: '/path', name: 'action')]
    public function action()
    {
    }
}
CODE_SAMPLE
                ,
                [
                    self::ANNOTATION_TO_ATTRIBUTE => [
                        new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            Class_::class,
            Property::class,
            Param::class,
            ClassMethod::class,
            Function_::class,
            Closure::class,
            ArrowFunction::class,
        ];
    }

    /**
     * @param Class_|Property|Param|ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        // 1. generic tags
        $genericAttributeGroups = $this->processGenericTags($phpDocInfo);
        // 2. Doctrine annotation classes
        $annotationAttributeGroups = $this->processDoctrineAnnotationClasses($phpDocInfo);

        $attributeGroups = array_merge($genericAttributeGroups, $annotationAttributeGroups);
        if ($attributeGroups === []) {
            return null;
        }

        $node->attrGroups = array_merge($node->attrGroups, $attributeGroups);

        $this->convertedAnnotationToAttributeParentRemover->processPhpDocNode(
            $phpDocInfo->getPhpDocNode(),
            $this->annotationsToAttributes,
            self::SKIP_UNWRAP_ANNOTATIONS
        );

        return $node;
    }

    /**
     * @param array<string, AnnotationToAttribute[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $annotationsToAttributes = $configuration[self::ANNOTATION_TO_ATTRIBUTE] ?? [];
        Assert::allIsInstanceOf($annotationsToAttributes, AnnotationToAttribute::class);
        $this->annotationsToAttributes = $annotationsToAttributes;
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }

    /**
     * @return AttributeGroup[]
     */
    private function processGenericTags(PhpDocInfo $phpDocInfo): array
    {
        $attributeGroups = [];

        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (DocNode $docNode) use (
            &$attributeGroups,
            $phpDocInfo
        ): ?int {
            if (! $docNode instanceof PhpDocTagNode) {
                return null;
            }

            if (! $docNode->value instanceof GenericTagValueNode) {
                return null;
            }

            $tag = trim($docNode->name, '@');

            // not a basic one
            if (str_contains($tag, '\\')) {
                return null;
            }

            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                $desiredTag = $annotationToAttribute->getTag();
                if ($desiredTag !== $tag) {
                    continue;
                }

                $attributeGroups[] = $this->phpAttributeGroupFactory->createFromSimpleTag($annotationToAttribute);

                $phpDocInfo->markAsChanged();
                return PhpDocNodeTraverser::NODE_REMOVE;
            }

            return null;
        });

        return $attributeGroups;
    }

    /**
     * @return AttributeGroup[]
     */
    private function processDoctrineAnnotationClasses(PhpDocInfo $phpDocInfo): array
    {
        $doctrineTagAndAnnotationToAttributes = [];

        $phpDocNodeTraverser = new PhpDocNodeTraverser();

        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (
            DocNode $node
        ) use (&$doctrineTagAndAnnotationToAttributes, $phpDocInfo): ?int {
            $docNode = $this->doctrineAnnotationTagValueNodeAnalyzer->resolveDoctrineAnnotationTagValueNode($node);

            if (! $docNode instanceof DoctrineAnnotationTagValueNode) {
                return null;
            }

            if ($docNode->hasClassNames(self::SKIP_UNWRAP_ANNOTATIONS)) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }

            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                if (! $docNode->hasClassName($annotationToAttribute->getTag())) {
                    continue;
                }

                $this->inverseJoinColumnCorrector->correctInverseJoinColumn($annotationToAttribute, $docNode);

                if ($this->doctrineAnnotationTagValueNodeAnalyzer->isNested(
                    $docNode,
                    $this->annotationsToAttributes
                )) {
                    $stringValues = $this->getStringFromNestedDoctrineTagAnnotationToAttribute($docNode);
                    $newDoctrineTagValueNode = $this->resolveNewDoctrineTagValueNode($docNode, $stringValues);

                    $doctrineTagAndAnnotationToAttributes[] = new DoctrineTagAndAnnotationToAttribute(
                        $newDoctrineTagValueNode,
                        $annotationToAttribute
                    );

                    /** @var DoctrineAnnotationTagValueNode $docNode */
                    $doctrineTagAndAnnotationToAttributes = $this->addNestedDoctrineTagAndAnnotationToAttribute(
                        $docNode,
                        $doctrineTagAndAnnotationToAttributes
                    );
                } else {
                    $doctrineTagAndAnnotationToAttributes[] = new DoctrineTagAndAnnotationToAttribute(
                        $docNode,
                        $annotationToAttribute
                    );
                }

                $phpDocInfo->markAsChanged();

                // remove the original doctrine annotation, it becomes an attribute
                return PhpDocNodeTraverser::NODE_REMOVE;
            }

            return null;
        });

        return $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes);
    }

    /**
     * @param string[] $stringValues
     */
    private function resolveNewDoctrineTagValueNode(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        array $stringValues
    ): DoctrineAnnotationTagValueNode {
        if ($stringValues === []) {
            return new DoctrineAnnotationTagValueNode($doctrineAnnotationTagValueNode->identifierTypeNode);
        }

        return new DoctrineAnnotationTagValueNode(
            $doctrineAnnotationTagValueNode->identifierTypeNode,
            null,
            $stringValues
        );
    }

    /**
     * @return string[]
     */
    private function getStringFromNestedDoctrineTagAnnotationToAttribute(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): array {
        $values = $doctrineAnnotationTagValueNode->getValues();

        return array_filter($values, 'is_string');
    }

    /**
     * @param DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     * @return DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     */
    private function addNestedDoctrineTagAndAnnotationToAttribute(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        array $doctrineTagAndAnnotationToAttributes
    ): array {
        $values = $doctrineAnnotationTagValueNode->getValues();
        foreach ($values as $value) {
            if (is_string($value)) {
                $originalValue = new DoctrineAnnotationTagValueNode(new IdentifierTypeNode($value));
                $doctrineTagAndAnnotationToAttributes = $this->collectDoctrineTagAndAnnotationToAttributes(
                    $originalValue,
                    $doctrineTagAndAnnotationToAttributes
                );

                continue;
            }

            $originalValues = $value->getOriginalValues();
            foreach ($originalValues as $originalValue) {
                $doctrineTagAndAnnotationToAttributes = $this->collectDoctrineTagAndAnnotationToAttributes(
                    $originalValue,
                    $doctrineTagAndAnnotationToAttributes
                );
            }
        }

        return $doctrineTagAndAnnotationToAttributes;
    }

    /**
     * @param DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     * @return DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     */
    private function collectDoctrineTagAndAnnotationToAttributes(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        array $doctrineTagAndAnnotationToAttributes
    ): array {
        foreach ($this->annotationsToAttributes as $annotationToAttribute) {
            $tag = $annotationToAttribute->getTag();
            if (! $doctrineAnnotationTagValueNode->hasClassName($tag)) {
                continue;
            }

            $doctrineTagAndAnnotationToAttributes[] = new DoctrineTagAndAnnotationToAttribute(
                $doctrineAnnotationTagValueNode,
                $annotationToAttribute
            );
        }

        return $doctrineTagAndAnnotationToAttributes;
    }
}
