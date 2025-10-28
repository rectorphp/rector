<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\FloatType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Enum\ClassName;
use Rector\Php74\Guard\MakePropertyTypedGuard;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Util\StringUtils;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\TypedPropertyFromJMSSerializerAttributeTypeRector\TypedPropertyFromJMSSerializerAttributeTypeRectorTest
 */
final class TypedPropertyFromJMSSerializerAttributeTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private MakePropertyTypedGuard $makePropertyTypedGuard;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private ScalarStringToTypeMapper $scalarStringToTypeMapper;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private AttributeFinder $attributeFinder;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private VarTagRemover $varTagRemover;
    public function __construct(MakePropertyTypedGuard $makePropertyTypedGuard, ReflectionResolver $reflectionResolver, ValueResolver $valueResolver, PhpAttributeAnalyzer $phpAttributeAnalyzer, ScalarStringToTypeMapper $scalarStringToTypeMapper, StaticTypeMapper $staticTypeMapper, AttributeFinder $attributeFinder, PhpDocInfoFactory $phpDocInfoFactory, VarTagRemover $varTagRemover)
    {
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
        $this->reflectionResolver = $reflectionResolver;
        $this->valueResolver = $valueResolver;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->attributeFinder = $attributeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->varTagRemover = $varTagRemover;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add typed property from JMS Serializer Type attribute', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    #[\JMS\Serializer\Annotation\Type('string')]
    private $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    #[\JMS\Serializer\Annotation\Type('string')]
    private ?string $name = null;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->hasAtLeastOneUntypedPropertyUsingJmsAttribute($node)) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if ($this->shouldSkipProperty($property, $classReflection)) {
                continue;
            }
            $typeValue = $this->resolveAttributeType($property);
            if (!is_string($typeValue)) {
                continue;
            }
            // skip generic iterable types
            if (strpos($typeValue, '<') !== \false) {
                continue;
            }
            $propertyTypeNode = $this->createTypeNode($typeValue, $property);
            if (!$propertyTypeNode instanceof Identifier && !$propertyTypeNode instanceof FullyQualified) {
                continue;
            }
            $property->type = new NullableType($propertyTypeNode);
            $property->props[0]->default = $this->nodeFactory->createNull();
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function resolveAttributeType(Property $property): ?string
    {
        $jmsTypeAttribute = $this->attributeFinder->findAttributeByClass($property, ClassName::JMS_TYPE);
        if (!$jmsTypeAttribute instanceof Attribute) {
            return null;
        }
        $typeValue = $this->valueResolver->getValue($jmsTypeAttribute->args[0]->value);
        if (!is_string($typeValue)) {
            return null;
        }
        if (StringUtils::isMatch($typeValue, '#DateTime\<(.*?)\>#')) {
            // special case for DateTime, which is not a scalar type
            return 'DateTime';
        }
        return $typeValue;
    }
    private function hasAtLeastOneUntypedPropertyUsingJmsAttribute(Class_ $class): bool
    {
        foreach ($class->getProperties() as $property) {
            if ($property->type instanceof Node) {
                continue;
            }
            if ($this->attributeFinder->hasAttributeByClasses($property, [ClassName::JMS_TYPE])) {
                return \true;
            }
        }
        return \false;
    }
    private function createTypeNode(string $typeValue, Property $property): ?Node
    {
        if ($typeValue === 'float') {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            // fallback to string, as most likely string representation of float
            if ($propertyPhpDocInfo instanceof PhpDocInfo && $propertyPhpDocInfo->getVarType() instanceof StringType) {
                $this->varTagRemover->removeVarTag($property);
                return new Identifier('string');
            }
        }
        if ($typeValue === 'string') {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            // fallback to string, as most likely string representation of float
            if ($propertyPhpDocInfo instanceof PhpDocInfo && $propertyPhpDocInfo->getVarType() instanceof FloatType) {
                $this->varTagRemover->removeVarTag($property);
                return new Identifier('float');
            }
        }
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeValue);
        if ($type instanceof MixedType) {
            // fallback to object type
            $type = new ObjectType($typeValue);
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
    }
    private function shouldSkipProperty(Property $property, ClassReflection $classReflection): bool
    {
        if ($property->type instanceof Node || $property->props[0]->default instanceof Expr) {
            return \true;
        }
        if (!$this->phpAttributeAnalyzer->hasPhpAttribute($property, ClassName::JMS_TYPE)) {
            return \true;
        }
        // this will be most likely collection, not single type
        if ($this->phpAttributeAnalyzer->hasPhpAttributes($property, array_merge(CollectionMapping::TO_MANY_CLASSES, CollectionMapping::TO_ONE_CLASSES))) {
            return \true;
        }
        return !$this->makePropertyTypedGuard->isLegal($property, $classReflection);
    }
}
