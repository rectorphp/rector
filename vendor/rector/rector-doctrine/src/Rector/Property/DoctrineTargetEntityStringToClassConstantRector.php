<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use RectorPrefix202301\Doctrine\ORM\Mapping\Embedded;
use RectorPrefix202301\Doctrine\ORM\Mapping\ManyToMany;
use RectorPrefix202301\Doctrine\ORM\Mapping\ManyToOne;
use RectorPrefix202301\Doctrine\ORM\Mapping\OneToMany;
use RectorPrefix202301\Doctrine\ORM\Mapping\OneToOne;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Doctrine\PhpDocParser\DoctrineClassAnnotationMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\DoctrineTargetEntityStringToClassConstantRectorTest
 */
final class DoctrineTargetEntityStringToClassConstantRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ATTRIBUTE_NAME__TARGET_ENTITY = 'targetEntity';
    /**
     * @var string
     */
    private const ATTRIBUTE_NAME__CLASS = 'class';
    /**
     * @var array<class-string<OneToMany|ManyToOne|OneToOne|ManyToMany|Embedded>, string>
     */
    private const VALID_DOCTRINE_CLASSES = ['Doctrine\\ORM\\Mapping\\OneToMany' => self::ATTRIBUTE_NAME__TARGET_ENTITY, 'Doctrine\\ORM\\Mapping\\ManyToOne' => self::ATTRIBUTE_NAME__TARGET_ENTITY, 'Doctrine\\ORM\\Mapping\\OneToOne' => self::ATTRIBUTE_NAME__TARGET_ENTITY, 'Doctrine\\ORM\\Mapping\\ManyToMany' => self::ATTRIBUTE_NAME__TARGET_ENTITY, 'Doctrine\\ORM\\Mapping\\Embedded' => self::ATTRIBUTE_NAME__CLASS];
    /**
     * @readonly
     * @var \Rector\Doctrine\PhpDocParser\DoctrineClassAnnotationMatcher
     */
    private $doctrineClassAnnotationMatcher;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    public function __construct(DoctrineClassAnnotationMatcher $doctrineClassAnnotationMatcher, AttributeFinder $attributeFinder)
    {
        $this->doctrineClassAnnotationMatcher = $doctrineClassAnnotationMatcher;
        $this->attributeFinder = $attributeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert targetEntities defined as String to <class>::class Constants in Doctrine Entities.', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="AnotherClass")
     */
    private readonly ?Collection $items;

    #[ORM\ManyToOne(targetEntity: "AnotherClass")]
    private readonly ?Collection $items2;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=\MyNamespace\Source\AnotherClass::class)
     */
    private readonly ?Collection $items;

    #[ORM\ManyToOne(targetEntity: \MyNamespace\Source\AnotherClass::class)]
    private readonly ?Collection $items2;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo instanceof PhpDocInfo) {
            $property = $this->changeTypeInAnnotationTypes($node, $phpDocInfo);
            $annotationDetected = $property !== null || $phpDocInfo->hasChanged();
            if ($annotationDetected) {
                return $property;
            }
        }
        return $this->changeTypeInAttributeTypes($node);
    }
    private function changeTypeInAttributeTypes(Property $property) : ?Property
    {
        $attribute = $this->attributeFinder->findAttributeByClasses($property, $this->getAttributeClasses());
        if (!$attribute instanceof Attribute) {
            return null;
        }
        return $this->changeTypeInAttribute($attribute, $property);
    }
    private function changeTypeInAttribute(Attribute $attribute, Property $property) : ?Property
    {
        $attributeName = $this->getAttributeName($attribute);
        foreach ($attribute->args as $arg) {
            $argName = $arg->name;
            if (!$argName instanceof Identifier) {
                continue;
            }
            if (!$this->isName($argName, $attributeName)) {
                continue;
            }
            /** @var string $value - Should always be string at this point */
            $value = $this->valueResolver->getValue($arg->value);
            $fullyQualified = $this->doctrineClassAnnotationMatcher->resolveExpectingDoctrineFQCN($value, $property);
            if ($fullyQualified === $value) {
                continue;
            }
            if ($fullyQualified === null) {
                continue;
            }
            $fullyQualifiedWithoutLeadingBackslash = \ltrim($fullyQualified, '\\');
            $arg->value = $this->nodeFactory->createClassConstFetch($fullyQualifiedWithoutLeadingBackslash, 'class');
            return $property;
        }
        return null;
    }
    private function changeTypeInAnnotationTypes(Property $property, PhpDocInfo $phpDocInfo) : ?Property
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses($this->getAttributeClasses());
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        return $this->processDoctrineToMany($doctrineAnnotationTagValueNode, $property);
    }
    private function processDoctrineToMany(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, Property $property) : ?Property
    {
        $key = $doctrineAnnotationTagValueNode->hasClassName('Doctrine\\ORM\\Mapping\\Embedded') ? self::ATTRIBUTE_NAME__CLASS : self::ATTRIBUTE_NAME__TARGET_ENTITY;
        $targetEntityArrayItemNode = $doctrineAnnotationTagValueNode->getValue($key);
        if (!$targetEntityArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        $targetEntity = $targetEntityArrayItemNode->value;
        if (!\is_string($targetEntity)) {
            return null;
        }
        // resolve to FQN
        $tagFullyQualifiedName = $this->doctrineClassAnnotationMatcher->resolveExpectingDoctrineFQCN($targetEntity, $property);
        if ($tagFullyQualifiedName === null) {
            return null;
        }
        if ($tagFullyQualifiedName === $targetEntity) {
            return null;
        }
        $currentArrayItemNode = $doctrineAnnotationTagValueNode->getValue($key);
        if (!$currentArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        // no quotes needed, it's a constants
        $currentArrayItemNode->kindValueQuoted = null;
        $currentArrayItemNode->value = '\\' . \ltrim($tagFullyQualifiedName, '\\') . '::class';
        $currentArrayItemNode->setAttribute('orig_node', null);
        return $property;
    }
    /**
     * @return class-string[]
     */
    private function getAttributeClasses() : array
    {
        return \array_keys(self::VALID_DOCTRINE_CLASSES);
    }
    private function getAttributeName(Attribute $attribute) : string
    {
        return self::VALID_DOCTRINE_CLASSES[$attribute->name->toString()];
    }
}
