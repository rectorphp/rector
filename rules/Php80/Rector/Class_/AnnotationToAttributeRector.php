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
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PHPStan\PhpDocParser\Ast\Node as DocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php80\NodeFactory\AttrGroupsFactory;
use Rector\Php80\NodeManipulator\AttributeGroupNamedArgumentManipulator;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\AttributeValueAndDocComment;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\AnnotationToAttributeRectorTest
 * @see \Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Php81NestedAttributesRectorTest
 * @see \Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\MultipleCallAnnotationToAttributeRectorTest
 */
final class AnnotationToAttributeRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private PhpAttributeGroupFactory $phpAttributeGroupFactory;
    /**
     * @readonly
     */
    private AttrGroupsFactory $attrGroupsFactory;
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private AttributeGroupNamedArgumentManipulator $attributeGroupNamedArgumentManipulator;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private \Rector\Php80\Rector\Class_\AttributeValueResolver $attributeValueResolver;
    /**
     * @var AnnotationToAttribute[]
     */
    private array $annotationsToAttributes = [];
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory, AttrGroupsFactory $attrGroupsFactory, PhpDocTagRemover $phpDocTagRemover, AttributeGroupNamedArgumentManipulator $attributeGroupNamedArgumentManipulator, UseImportsResolver $useImportsResolver, PhpAttributeAnalyzer $phpAttributeAnalyzer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider, \Rector\Php80\Rector\Class_\AttributeValueResolver $attributeValueResolver)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->attrGroupsFactory = $attrGroupsFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->attributeGroupNamedArgumentManipulator = $attributeGroupNamedArgumentManipulator;
        $this->useImportsResolver = $useImportsResolver;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->attributeValueResolver = $attributeValueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change annotation to attribute', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    /**
     * @Route("/path", name="action") api route
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
    #[Route(path: '/path', name: 'action')] // api route
    public function action()
    {
    }
}
CODE_SAMPLE
, [new AnnotationToAttribute('Symfony\\Component\\Routing\\Annotation\\Route')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, Property::class, Param::class, ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class, Interface_::class];
    }
    /**
     * @param Class_|Property|Param|ClassMethod|Function_|Closure|ArrowFunction|Interface_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->annotationsToAttributes === []) {
            throw new InvalidConfigurationException(\sprintf('The "%s" rule requires configuration.', self::class));
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $uses = $this->useImportsResolver->resolveBareUses();
        // 1. Doctrine annotation classes
        $annotationAttributeGroups = $this->processDoctrineAnnotationClasses($phpDocInfo, $uses);
        // 2. bare tags without annotation class, e.g. "@require"
        $genericAttributeGroups = $this->processGenericTags($phpDocInfo);
        $attributeGroups = \array_merge($annotationAttributeGroups, $genericAttributeGroups);
        if ($attributeGroups === []) {
            return null;
        }
        // 3. Reprint docblock
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        $this->attributeGroupNamedArgumentManipulator->decorate($attributeGroups);
        $node->attrGroups = \array_merge($node->attrGroups, $attributeGroups);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, AnnotationToAttribute::class);
        $this->annotationsToAttributes = $this->resolveWithChangedAttributesClass($configuration);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param AnnotationToAttribute[] $configuration
     * @return AnnotationToAttribute[] $configuration
     */
    private function resolveWithChangedAttributesClass(array $configuration) : array
    {
        foreach ($configuration as $config) {
            /** @var AnnotationToAttribute $config */
            if ($config->getAttributeClass() !== $config->getTag()) {
                // add to make sure apply after use statement changed
                $configuration[] = new AnnotationToAttribute($config->getAttributeClass(), $config->getAttributeClass(), $config->getClassReferenceFields(), $config->getUseValueAsAttributeArgument());
            }
        }
        return $configuration;
    }
    /**
     * @return AttributeGroup[]
     */
    private function processGenericTags(PhpDocInfo $phpDocInfo) : array
    {
        $attributeGroups = [];
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (DocNode $docNode) use(&$attributeGroups) {
            if (!$docNode instanceof PhpDocTagNode) {
                return null;
            }
            if (!$docNode->value instanceof GenericTagValueNode && !$docNode->value instanceof DoctrineAnnotationTagValueNode) {
                return null;
            }
            $tag = \trim($docNode->name, '@');
            // not a basic one
            if (\strpos($tag, '\\') !== \false) {
                return null;
            }
            foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                $desiredTag = $annotationToAttribute->getTag();
                if (\strtolower($desiredTag) !== \strtolower($tag)) {
                    continue;
                }
                // make sure the attribute class really exists to avoid error on early upgrade
                if (!$this->reflectionProvider->hasClass($annotationToAttribute->getAttributeClass())) {
                    continue;
                }
                $attributeValueAndDocComment = $this->attributeValueResolver->resolve($annotationToAttribute, $docNode);
                $attributeGroups[] = $this->phpAttributeGroupFactory->createFromSimpleTag($annotationToAttribute, $attributeValueAndDocComment instanceof AttributeValueAndDocComment ? $attributeValueAndDocComment->attributeValue : null);
                // keep partial original comment, if useful
                if ($attributeValueAndDocComment instanceof AttributeValueAndDocComment && $attributeValueAndDocComment->docComment) {
                    return new PhpDocTextNode($attributeValueAndDocComment->docComment);
                }
                return PhpDocNodeTraverser::NODE_REMOVE;
            }
            return null;
        });
        return $attributeGroups;
    }
    /**
     * @param Use_[] $uses
     * @return AttributeGroup[]
     */
    private function processDoctrineAnnotationClasses(PhpDocInfo $phpDocInfo, array $uses) : array
    {
        if ($phpDocInfo->getPhpDocNode()->children === []) {
            return [];
        }
        $doctrineTagAndAnnotationToAttributes = [];
        $doctrineTagValueNodes = [];
        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $doctrineTagValueNode = $phpDocChildNode->value;
            $annotationToAttribute = $this->matchAnnotationToAttribute($doctrineTagValueNode);
            if (!$annotationToAttribute instanceof AnnotationToAttribute) {
                continue;
            }
            if ($annotationToAttribute->getUseValueAsAttributeArgument()) {
                /* Will be processed by processGenericTags instead */
                continue;
            }
            if (!$this->isExistingAttributeClass($annotationToAttribute)) {
                continue;
            }
            $doctrineTagAndAnnotationToAttributes[] = new DoctrineTagAndAnnotationToAttribute($doctrineTagValueNode, $annotationToAttribute);
            $doctrineTagValueNodes[] = $doctrineTagValueNode;
        }
        $attributeGroups = $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes, $uses);
        if ($this->phpAttributeAnalyzer->hasRemoveArrayState($attributeGroups)) {
            return [];
        }
        foreach ($doctrineTagValueNodes as $doctrineTagValueNode) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineTagValueNode);
        }
        return $attributeGroups;
    }
    private function matchAnnotationToAttribute(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : ?\Rector\Php80\ValueObject\AnnotationToAttribute
    {
        foreach ($this->annotationsToAttributes as $annotationToAttribute) {
            if (!$doctrineAnnotationTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                continue;
            }
            return $annotationToAttribute;
        }
        return null;
    }
    private function isExistingAttributeClass(AnnotationToAttribute $annotationToAttribute) : bool
    {
        // make sure the attribute class really exists to avoid error on early upgrade
        if (!$this->reflectionProvider->hasClass($annotationToAttribute->getAttributeClass())) {
            return \false;
        }
        // make sure the class is marked as attribute
        $classReflection = $this->reflectionProvider->getClass($annotationToAttribute->getAttributeClass());
        return $classReflection->isAttributeClass();
    }
}
