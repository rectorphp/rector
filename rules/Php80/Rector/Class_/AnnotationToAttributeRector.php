<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeFactory\AttrGroupsFactory;
use Rector\Php80\PhpDocCleaner\ConvertedAnnotationToAttributeParentRemover;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use Webmozart\Assert\Assert;

/**
 * @changelog https://wiki.php.net/rfc/attributes_v2
 *
 * @see \Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\AnnotationToAttributeRectorTest
 */
final class AnnotationToAttributeRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ANNOTATION_TO_ATTRIBUTE = 'annotation_to_attribute';

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
        private PhpDocTagRemover $phpDocTagRemover,
        private ConvertedAnnotationToAttributeParentRemover $convertedAnnotationToAttributeParentRemover,
        private AttrGroupsFactory $attrGroupsFactory
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
            ClassMethod::class,
            Function_::class,
            Closure::class,
            ArrowFunction::class,
        ];
    }

    /**
     * @param Class_|Property|ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::ATTRIBUTES)) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        $originalAttrGroupsCount = count($node->attrGroups);

        // 1. generic tags
        $this->processGenericTags($phpDocInfo, $node);

        // 2. Doctrine annotation classes
        $this->processDoctrineAnnotationClasses($phpDocInfo, $node);

        $currentAttrGroupsCount = count($node->attrGroups);

        // something has changed
        if ($originalAttrGroupsCount !== $currentAttrGroupsCount) {
            return $node;
        }

        return null;
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

    private function isFoundGenericTag(
        PhpDocInfo $phpDocInfo,
        PhpDocTagValueNode $phpDocTagValueNode,
        string $annotationToAttributeTag
    ): bool {
        if (! $phpDocInfo->hasByName($annotationToAttributeTag)) {
            return false;
        }

        return $phpDocTagValueNode instanceof GenericTagValueNode;
    }

    private function processGenericTags(
        PhpDocInfo $phpDocInfo,
        ClassMethod | Function_ | Closure | ArrowFunction | Property | Class_ $node
    ): void {
        foreach ($phpDocInfo->getAllTags() as $phpDocTagNode) {
            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                $desiredTag = $annotationToAttribute->getTag();

                // not a basic one
                if (str_contains($desiredTag, '\\')) {
                    continue;
                }

                if (! $this->isFoundGenericTag($phpDocInfo, $phpDocTagNode->value, $desiredTag)) {
                    continue;
                }

                // 1. remove php-doc tag
                $this->phpDocTagRemover->removeByName($phpDocInfo, $desiredTag);

                // 2. add attributes
                $node->attrGroups[] = $this->phpAttributeGroupFactory->createFromSimpleTag($annotationToAttribute);
            }
        }
    }

    private function processDoctrineAnnotationClasses(
        PhpDocInfo $phpDocInfo,
        ClassMethod | Function_ | Closure | ArrowFunction | Property | Class_ $node
    ): void {
        if ($this->shouldSkip($phpDocInfo)) {
            return;
        }

        $doctrineTagAndAnnotationToAttributes = [];

        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function ($node) use (
            &$doctrineTagAndAnnotationToAttributes,
            $phpDocInfo
        ) {
            if (! $node instanceof DoctrineAnnotationTagValueNode) {
                return $node;
            }

            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                if (! $node->hasClassName($annotationToAttribute->getTag())) {
                    continue;
                }

                $doctrineTagAndAnnotationToAttributes[] = new DoctrineTagAndAnnotationToAttribute(
                    $node,
                    $annotationToAttribute
                );

                $phpDocInfo->markAsChanged();

                // remove the original doctrine annotation, it becomes an attribute
                return PhpDocNodeTraverser::NODE_REMOVE;
            }

            return $node;
        });

        $attrGroups = $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes);
        if ($attrGroups === []) {
            return;
        }

        $node->attrGroups = $attrGroups;
        $this->convertedAnnotationToAttributeParentRemover->processPhpDocNode(
            $phpDocInfo->getPhpDocNode(),
            $this->annotationsToAttributes
        );
    }

    private function shouldSkip(PhpDocInfo $phpDocInfo): bool
    {
        return $phpDocInfo->hasByAnnotationClasses(self::SKIP_UNWRAP_ANNOTATIONS);
    }
}
