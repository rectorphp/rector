<?php

declare (strict_types=1);
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
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
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
use RectorPrefix20210708\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use RectorPrefix20210708\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/attributes_v2
 *
 * @see \Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\AnnotationToAttributeRectorTest
 */
final class AnnotationToAttributeRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ANNOTATION_TO_ATTRIBUTE = 'annotation_to_attribute';
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
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @var \Rector\Php80\PhpDocCleaner\ConvertedAnnotationToAttributeParentRemover
     */
    private $convertedAnnotationToAttributeParentRemover;
    /**
     * @var \Rector\Php80\NodeFactory\AttrGroupsFactory
     */
    private $attrGroupsFactory;
    public function __construct(\Rector\PhpAttribute\Printer\PhpAttributeGroupFactory $phpAttributeGroupFactory, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\Php80\PhpDocCleaner\ConvertedAnnotationToAttributeParentRemover $convertedAnnotationToAttributeParentRemover, \Rector\Php80\NodeFactory\AttrGroupsFactory $attrGroupsFactory)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->convertedAnnotationToAttributeParentRemover = $convertedAnnotationToAttributeParentRemover;
        $this->attrGroupsFactory = $attrGroupsFactory;
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
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Expr\ArrowFunction::class];
    }
    /**
     * @param Class_|Property|ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::ATTRIBUTES)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        $originalAttrGroupsCount = \count($node->attrGroups);
        // 1. generic tags
        $this->processGenericTags($phpDocInfo, $node);
        // 2. Doctrine annotation classes
        $this->processDoctrineAnnotationClasses($phpDocInfo, $node);
        $currentAttrGroupsCount = \count($node->attrGroups);
        // something has changed
        if ($originalAttrGroupsCount !== $currentAttrGroupsCount) {
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, AnnotationToAttribute[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $annotationsToAttributes = $configuration[self::ANNOTATION_TO_ATTRIBUTE] ?? [];
        \RectorPrefix20210708\Webmozart\Assert\Assert::allIsInstanceOf($annotationsToAttributes, \Rector\Php80\ValueObject\AnnotationToAttribute::class);
        $this->annotationsToAttributes = $annotationsToAttributes;
    }
    private function isFoundGenericTag(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $phpDocTagValueNode, string $annotationToAttributeTag) : bool
    {
        if (!$phpDocInfo->hasByName($annotationToAttributeTag)) {
            return \false;
        }
        return $phpDocTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\Class_ $node
     */
    private function processGenericTags(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, $node) : void
    {
        foreach ($phpDocInfo->getAllTags() as $phpDocTagNode) {
            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                $desiredTag = $annotationToAttribute->getTag();
                // not a basic one
                if (\strpos($desiredTag, '\\') !== \false) {
                    continue;
                }
                if (!$this->isFoundGenericTag($phpDocInfo, $phpDocTagNode->value, $desiredTag)) {
                    continue;
                }
                // 1. remove php-doc tag
                $this->phpDocTagRemover->removeByName($phpDocInfo, $desiredTag);
                // 2. add attributes
                $node->attrGroups[] = $this->phpAttributeGroupFactory->createFromSimpleTag($annotationToAttribute);
            }
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\Class_ $node
     */
    private function processDoctrineAnnotationClasses(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, $node) : void
    {
        if ($this->shouldSkip($phpDocInfo)) {
            return;
        }
        $doctrineTagAndAnnotationToAttributes = [];
        $phpDocNodeTraverser = new \RectorPrefix20210708\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function ($node) use(&$doctrineTagAndAnnotationToAttributes, $phpDocInfo) : ?int {
            if ($node instanceof \Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode && $node->value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                $doctrineAnnotationTagValueNode = $node->value;
            } elseif ($node instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                $doctrineAnnotationTagValueNode = $node;
            } else {
                return null;
            }
            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                if (!$doctrineAnnotationTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                    continue;
                }
                $doctrineTagAndAnnotationToAttributes[] = new \Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute($doctrineAnnotationTagValueNode, $annotationToAttribute);
                $phpDocInfo->markAsChanged();
                // remove the original doctrine annotation, it becomes an attribute
                return \RectorPrefix20210708\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
            }
            return null;
        });
        $attrGroups = $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes);
        if ($attrGroups === []) {
            return;
        }
        $node->attrGroups = $attrGroups;
        $this->convertedAnnotationToAttributeParentRemover->processPhpDocNode($phpDocInfo->getPhpDocNode(), $this->annotationsToAttributes);
    }
    private function shouldSkip(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        return $phpDocInfo->hasByAnnotationClasses(self::SKIP_UNWRAP_ANNOTATIONS);
    }
}
