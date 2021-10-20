<?php

declare (strict_types=1);
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
use RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/attributes_v2
 *
 * @see \Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\AnnotationToAttributeRectorTest
 */
final class AnnotationToAttributeRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface, \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    public const ANNOTATION_TO_ATTRIBUTE = 'annotations_to_attributes';
    /**
     * List of annotations that should not be unwrapped
     * @var string[]
     */
    private const SKIP_UNWRAP_ANNOTATIONS = ['Symfony\\Component\\Validator\\Constraints\\All', 'Symfony\\Component\\Validator\\Constraints\\AtLeastOneOf', 'Symfony\\Component\\Validator\\Constraints\\Collection', 'Symfony\\Component\\Validator\\Constraints\\Sequentially'];
    /**
     * @var AnnotationToAttribute[]
     */
    private $annotationsToAttributes = [];
    /**
     * @var \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @var \Rector\Php80\PhpDocCleaner\ConvertedAnnotationToAttributeParentRemover
     */
    private $convertedAnnotationToAttributeParentRemover;
    /**
     * @var \Rector\Php80\NodeFactory\AttrGroupsFactory
     */
    private $attrGroupsFactory;
    /**
     * @var \Rector\BetterPhpDocParser\AnnotationAnalyzer\DoctrineAnnotationTagValueNodeAnalyzer
     */
    private $doctrineAnnotationTagValueNodeAnalyzer;
    public function __construct(\Rector\PhpAttribute\Printer\PhpAttributeGroupFactory $phpAttributeGroupFactory, \Rector\Php80\PhpDocCleaner\ConvertedAnnotationToAttributeParentRemover $convertedAnnotationToAttributeParentRemover, \Rector\Php80\NodeFactory\AttrGroupsFactory $attrGroupsFactory, \Rector\BetterPhpDocParser\AnnotationAnalyzer\DoctrineAnnotationTagValueNodeAnalyzer $doctrineAnnotationTagValueNodeAnalyzer)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->convertedAnnotationToAttributeParentRemover = $convertedAnnotationToAttributeParentRemover;
        $this->attrGroupsFactory = $attrGroupsFactory;
        $this->doctrineAnnotationTagValueNodeAnalyzer = $doctrineAnnotationTagValueNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change annotation to attribute', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    #[Route(path: '/path', name: 'action')]
    public function action()
    {
    }
}
CODE_SAMPLE
, [self::ANNOTATION_TO_ATTRIBUTE => [new \Rector\Php80\ValueObject\AnnotationToAttribute('Symfony\\Component\\Routing\\Annotation\\Route')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Param::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Expr\ArrowFunction::class];
    }
    /**
     * @param Class_|Property|Param|ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        // 1. generic tags
        $genericAttributeGroups = $this->processGenericTags($phpDocInfo);
        // 2. Doctrine annotation classes
        $annotationAttributeGroups = $this->processDoctrineAnnotationClasses($phpDocInfo);
        $attributeGroups = \array_merge($genericAttributeGroups, $annotationAttributeGroups);
        if ($attributeGroups === []) {
            return null;
        }
        $node->attrGroups = \array_merge($node->attrGroups, $attributeGroups);
        $this->convertedAnnotationToAttributeParentRemover->processPhpDocNode($phpDocInfo->getPhpDocNode(), $this->annotationsToAttributes, self::SKIP_UNWRAP_ANNOTATIONS);
        return $node;
    }
    /**
     * @param array<string, AnnotationToAttribute[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $annotationsToAttributes = $configuration[self::ANNOTATION_TO_ATTRIBUTE] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($annotationsToAttributes, \Rector\Php80\ValueObject\AnnotationToAttribute::class);
        $this->annotationsToAttributes = $annotationsToAttributes;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @return AttributeGroup[]
     */
    private function processGenericTags(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : array
    {
        $attributeGroups = [];
        $phpDocNodeTraverser = new \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (\PHPStan\PhpDocParser\Ast\Node $docNode) use(&$attributeGroups, $phpDocInfo) : ?int {
            if (!$docNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                return null;
            }
            if (!$docNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode) {
                return null;
            }
            $tag = \trim($docNode->name, '@');
            // not a basic one
            if (\strpos($tag, '\\') !== \false) {
                return null;
            }
            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                $desiredTag = $annotationToAttribute->getTag();
                if ($desiredTag !== $tag) {
                    continue;
                }
                $attributeGroups[] = $this->phpAttributeGroupFactory->createFromSimpleTag($annotationToAttribute);
                $phpDocInfo->markAsChanged();
                return \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
            }
            return null;
        });
        return $attributeGroups;
    }
    /**
     * @return AttributeGroup[]
     */
    private function processDoctrineAnnotationClasses(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : array
    {
        $doctrineTagAndAnnotationToAttributes = [];
        $phpDocNodeTraverser = new \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (\PHPStan\PhpDocParser\Ast\Node $node) use(&$doctrineTagAndAnnotationToAttributes, $phpDocInfo) : ?int {
            $docNode = $this->doctrineAnnotationTagValueNodeAnalyzer->resolveDoctrineAnnotationTagValueNode($node);
            if (!$docNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                return null;
            }
            if ($docNode->hasClassNames(self::SKIP_UNWRAP_ANNOTATIONS)) {
                return \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                if (!$docNode->hasClassName($annotationToAttribute->getTag())) {
                    continue;
                }
                if ($this->doctrineAnnotationTagValueNodeAnalyzer->isNested($docNode, $this->annotationsToAttributes)) {
                    $newDoctrineTagValueNode = new \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode($docNode->identifierTypeNode);
                    $doctrineTagAndAnnotationToAttributes[] = new \Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute($newDoctrineTagValueNode, $annotationToAttribute);
                    /** @var DoctrineAnnotationTagValueNode $docNode */
                    $doctrineTagAndAnnotationToAttributes = $this->addNestedDoctrineTagAndAnnotationToAttribute($docNode, $doctrineTagAndAnnotationToAttributes);
                } else {
                    $doctrineTagAndAnnotationToAttributes[] = new \Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute($docNode, $annotationToAttribute);
                }
                $phpDocInfo->markAsChanged();
                // remove the original doctrine annotation, it becomes an attribute
                return \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
            }
            return null;
        });
        return $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes);
    }
    /**
     * @param DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     * @return DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     */
    private function addNestedDoctrineTagAndAnnotationToAttribute(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, array $doctrineTagAndAnnotationToAttributes) : array
    {
        $values = $doctrineAnnotationTagValueNode->getValues();
        foreach ($values as $value) {
            $originalValues = $value->getOriginalValues();
            foreach ($originalValues as $originalValue) {
                $doctrineTagAndAnnotationToAttributes = $this->collectDoctrineTagAndAnnotationToAttributes($originalValue, $doctrineTagAndAnnotationToAttributes);
            }
        }
        return $doctrineTagAndAnnotationToAttributes;
    }
    /**
     * @param DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     * @return DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     */
    private function collectDoctrineTagAndAnnotationToAttributes(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, array $doctrineTagAndAnnotationToAttributes) : array
    {
        foreach ($this->annotationsToAttributes as $annotationToAttribute) {
            $tag = $annotationToAttribute->getTag();
            if (!$doctrineAnnotationTagValueNode->hasClassName($tag)) {
                continue;
            }
            $doctrineTagAndAnnotationToAttributes[] = new \Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute($doctrineAnnotationTagValueNode, $annotationToAttribute);
        }
        return $doctrineTagAndAnnotationToAttributes;
    }
}
