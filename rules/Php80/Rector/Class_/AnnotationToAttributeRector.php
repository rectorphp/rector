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
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeFactory\AttrGroupsFactory;
use Rector\Php80\PhpDoc\PhpDocNodeFinder;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211123\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use RectorPrefix20211123\Webmozart\Assert\Assert;
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
     * @var AnnotationToAttribute[]
     */
    private $annotationsToAttributes = [];
    /**
     * @var \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @var \Rector\Php80\NodeFactory\AttrGroupsFactory
     */
    private $attrGroupsFactory;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @var \Rector\Php80\PhpDoc\PhpDocNodeFinder
     */
    private $phpDocNodeFinder;
    public function __construct(\Rector\PhpAttribute\Printer\PhpAttributeGroupFactory $phpAttributeGroupFactory, \Rector\Php80\NodeFactory\AttrGroupsFactory $attrGroupsFactory, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\Php80\PhpDoc\PhpDocNodeFinder $phpDocNodeFinder)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->attrGroupsFactory = $attrGroupsFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpDocNodeFinder = $phpDocNodeFinder;
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
        return $node;
    }
    /**
     * @param array<string, AnnotationToAttribute[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $annotationsToAttributes = $configuration[self::ANNOTATION_TO_ATTRIBUTE] ?? [];
        \RectorPrefix20211123\Webmozart\Assert\Assert::allIsInstanceOf($annotationsToAttributes, \Rector\Php80\ValueObject\AnnotationToAttribute::class);
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
        $phpDocNodeTraverser = new \RectorPrefix20211123\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
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
                return \RectorPrefix20211123\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
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
        if ($phpDocInfo->getPhpDocNode()->children === []) {
            return [];
        }
        $doctrineTagAndAnnotationToAttributes = [];
        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                continue;
            }
            $doctrineTagValueNode = $phpDocChildNode->value;
            $annotationToAttribute = $this->matchAnnotationToAttribute($doctrineTagValueNode);
            if (!$annotationToAttribute instanceof \Rector\Php80\ValueObject\AnnotationToAttribute) {
                continue;
            }
            $nestedDoctrineAnnotationTagValueNodes = $this->phpDocNodeFinder->findByType($doctrineTagValueNode, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode::class);
            // depends on PHP 8.1+ - nested values, skip for now
            if ($nestedDoctrineAnnotationTagValueNodes !== [] && !$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::NEW_INITIALIZERS)) {
                continue;
            }
            $doctrineTagAndAnnotationToAttributes[] = new \Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute($doctrineTagValueNode, $annotationToAttribute);
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineTagValueNode);
        }
        return $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes);
    }
    /**
     * @return \Rector\Php80\ValueObject\AnnotationToAttribute|null
     */
    private function matchAnnotationToAttribute(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        foreach ($this->annotationsToAttributes as $annotationToAttribute) {
            if (!$doctrineAnnotationTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                continue;
            }
            return $annotationToAttribute;
        }
        return null;
    }
}
