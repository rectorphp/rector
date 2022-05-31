<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use RectorPrefix20220531\Doctrine\ORM\Mapping\Embedded;
use RectorPrefix20220531\Doctrine\ORM\Mapping\ManyToMany;
use RectorPrefix20220531\Doctrine\ORM\Mapping\ManyToOne;
use RectorPrefix20220531\Doctrine\ORM\Mapping\OneToMany;
use RectorPrefix20220531\Doctrine\ORM\Mapping\OneToOne;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\DoctrineTargetEntityStringToClassConstantRectorTest
 */
final class DoctrineTargetEntityStringToClassConstantRector extends \Rector\Core\Rector\AbstractRector
{
    private const ATTRIBUTE_NAME__TARGET_ENTITY = 'targetEntity';
    private const ATTRIBUTE_NAME__CLASS = 'class';
    /**
     * @var array<class-string<OneToMany|ManyToOne|OneToOne|ManyToMany|Embedded>, string>
     */
    private const VALID_DOCTRINE_CLASSES = ['Doctrine\\ORM\\Mapping\\OneToMany' => self::ATTRIBUTE_NAME__TARGET_ENTITY, 'Doctrine\\ORM\\Mapping\\ManyToOne' => self::ATTRIBUTE_NAME__TARGET_ENTITY, 'Doctrine\\ORM\\Mapping\\OneToOne' => self::ATTRIBUTE_NAME__TARGET_ENTITY, 'Doctrine\\ORM\\Mapping\\ManyToMany' => self::ATTRIBUTE_NAME__TARGET_ENTITY, 'Doctrine\\ORM\\Mapping\\Embedded' => self::ATTRIBUTE_NAME__CLASS];
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher $classAnnotationMatcher, \Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->attributeFinder = $attributeFinder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert targetEntities defined as String to <class>::class Constants in Doctrine Entities.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo !== null) {
            $property = $this->changeTypeInAnnotationTypes($node, $phpDocInfo);
            $hasChanged = $property !== null || $phpDocInfo->hasChanged();
        }
        return $this->changeTypeInAttributeTypes($node, $hasChanged);
    }
    private function changeTypeInAttributeTypes(\PhpParser\Node\Stmt\Property $property, bool $hasChanged) : ?\PhpParser\Node\Stmt\Property
    {
        $attribute = $this->attributeFinder->findAttributeByClasses($property, $this->getAttributeClasses());
        if (!$attribute instanceof \PhpParser\Node\Attribute) {
            return $hasChanged ? $property : null;
        }
        $attributeName = $this->getAttributeName($attribute);
        foreach ($attribute->args as $arg) {
            $argName = $arg->name;
            if (!$argName instanceof \PhpParser\Node\Identifier) {
                continue;
            }
            if (!$this->isName($argName, $attributeName)) {
                continue;
            }
            /** @var string $value - Should always be string at this point */
            $value = $this->valueResolver->getValue($arg->value);
            $fullyQualified = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($value, $property);
            if ($fullyQualified === $value) {
                continue;
            }
            $arg->value = $this->nodeFactory->createClassConstFetch($fullyQualified, 'class');
            return $property;
        }
        return $hasChanged ? $property : null;
    }
    private function changeTypeInAnnotationTypes(\PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : ?\PhpParser\Node\Stmt\Property
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses($this->getAttributeClasses());
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        return $this->processDoctrineToMany($doctrineAnnotationTagValueNode, $property);
    }
    private function processDoctrineToMany(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \PhpParser\Node\Stmt\Property $property) : ?\PhpParser\Node\Stmt\Property
    {
        $key = $doctrineAnnotationTagValueNode->hasClassName('Doctrine\\ORM\\Mapping\\Embedded') ? self::ATTRIBUTE_NAME__CLASS : self::ATTRIBUTE_NAME__TARGET_ENTITY;
        /** @var ?string $targetEntity */
        $targetEntity = $doctrineAnnotationTagValueNode->getValueWithoutQuotes($key);
        if ($targetEntity === null) {
            return null;
        }
        // resolve to FQN
        $tagFullyQualifiedName = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($targetEntity, $property);
        if ($tagFullyQualifiedName === $targetEntity) {
            return null;
        }
        $doctrineAnnotationTagValueNode->removeValue($key);
        $doctrineAnnotationTagValueNode->values[$key] = '\\' . $tagFullyQualifiedName . '::class';
        return $property;
    }
    /**
     * @return class-string[]
     */
    private function getAttributeClasses() : array
    {
        return \array_keys(self::VALID_DOCTRINE_CLASSES);
    }
    private function getAttributeName(\PhpParser\Node\Attribute $attribute) : string
    {
        return self::VALID_DOCTRINE_CLASSES[$attribute->name->toString()];
    }
}
