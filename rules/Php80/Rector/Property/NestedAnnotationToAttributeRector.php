<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeFactory\NestedAttrGroupsFactory;
use Rector\Php80\ValueObject\AnnotationPropertyToAttributeClass;
use Rector\Php80\ValueObject\NestedAnnotationToAttribute;
use Rector\Php80\ValueObject\NestedDoctrineTagAndAnnotationToAttribute;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202301\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php80\Rector\Property\NestedAnnotationToAttributeRector\NestedAnnotationToAttributeRectorTest
 *
 * @changelog https://www.doctrine-project.org/projects/doctrine-orm/en/2.13/reference/attributes-reference.html#joincolumn-inversejoincolumn
 */
final class NestedAnnotationToAttributeRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var NestedAnnotationToAttribute[]
     */
    private $nestedAnnotationsToAttributes = [];
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\NestedAttrGroupsFactory
     */
    private $nestedAttrGroupsFactory;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
    public function __construct(UseImportsResolver $useImportsResolver, PhpDocTagRemover $phpDocTagRemover, NestedAttrGroupsFactory $nestedAttrGroupsFactory, UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->useImportsResolver = $useImportsResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->nestedAttrGroupsFactory = $nestedAttrGroupsFactory;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changed nested annotations to attributes', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SomeEntity
{
    /**
     * @ORM\JoinTable(name="join_table_name",
     *     joinColumns={@ORM\JoinColumn(name="origin_id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="target_id")}
     * )
     */
    private $collection;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SomeEntity
{
    #[ORM\JoinTable(name: 'join_table_name')]
    #[ORM\JoinColumn(name: 'origin_id')]
    #[ORM\InverseJoinColumn(name: 'target_id')]
    private $collection;
}
CODE_SAMPLE
, [[new NestedAnnotationToAttribute('Doctrine\\ORM\\Mapping\\JoinTable', [new AnnotationPropertyToAttributeClass('Doctrine\\ORM\\Mapping\\JoinColumn', 'joinColumns'), new AnnotationPropertyToAttributeClass('Doctrine\\ORM\\Mapping\\InverseJoinColumn', 'inverseJoinColumns')])]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class, Class_::class, Param::class];
    }
    /**
     * @param Property|Class_|Param $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $uses = $this->useImportsResolver->resolveBareUsesForNode($node);
        $attributeGroups = $this->transformDoctrineAnnotationClassesToAttributeGroups($phpDocInfo, $uses);
        if ($attributeGroups === []) {
            return null;
        }
        $node->attrGroups = \array_merge($node->attrGroups, $attributeGroups);
        $this->completeExtraUseImports($attributeGroups);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsInstanceOf($configuration, NestedAnnotationToAttribute::class);
        $this->nestedAnnotationsToAttributes = $configuration;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_80;
    }
    /**
     * @param Node\Stmt\Use_[] $uses
     * @return AttributeGroup[]
     */
    private function transformDoctrineAnnotationClassesToAttributeGroups(PhpDocInfo $phpDocInfo, array $uses) : array
    {
        if ($phpDocInfo->getPhpDocNode()->children === []) {
            return [];
        }
        $nestedDoctrineTagAndAnnotationToAttributes = [];
        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $doctrineTagValueNode = $phpDocChildNode->value;
            $nestedAnnotationToAttribute = $this->matchAnnotationToAttribute($doctrineTagValueNode);
            if (!$nestedAnnotationToAttribute instanceof NestedAnnotationToAttribute) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineTagValueNode);
            $nestedDoctrineTagAndAnnotationToAttributes[] = new NestedDoctrineTagAndAnnotationToAttribute($doctrineTagValueNode, $nestedAnnotationToAttribute);
        }
        return $this->nestedAttrGroupsFactory->create($nestedDoctrineTagAndAnnotationToAttributes, $uses);
    }
    private function matchAnnotationToAttribute(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : ?\Rector\Php80\ValueObject\NestedAnnotationToAttribute
    {
        foreach ($this->nestedAnnotationsToAttributes as $nestedAnnotationToAttribute) {
            $doctrineResolvedClass = $doctrineAnnotationTagValueNode->identifierTypeNode->getAttribute(PhpDocAttributeKey::RESOLVED_CLASS);
            if ($doctrineResolvedClass !== $nestedAnnotationToAttribute->getTag()) {
                continue;
            }
            return $nestedAnnotationToAttribute;
        }
        return null;
    }
    /**
     * @param AttributeGroup[] $attributeGroups
     */
    private function completeExtraUseImports(array $attributeGroups) : void
    {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attr) {
                $namespacedAttrName = $attr->name->getAttribute(AttributeKey::EXTRA_USE_IMPORT);
                if (!\is_string($namespacedAttrName)) {
                    continue;
                }
                $this->useNodesToAddCollector->addUseImport(new FullyQualifiedObjectType($namespacedAttrName));
            }
        }
    }
}
