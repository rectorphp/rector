<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
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
    public function __construct(MakePropertyTypedGuard $makePropertyTypedGuard, ReflectionResolver $reflectionResolver, ValueResolver $valueResolver, PhpAttributeAnalyzer $phpAttributeAnalyzer, ScalarStringToTypeMapper $scalarStringToTypeMapper, StaticTypeMapper $staticTypeMapper)
    {
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
        $this->reflectionResolver = $reflectionResolver;
        $this->valueResolver = $valueResolver;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
        $this->staticTypeMapper = $staticTypeMapper;
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
        $hasChanged = \false;
        $classReflection = null;
        foreach ($node->getProperties() as $property) {
            if ($property->type instanceof Node || $property->props[0]->default instanceof Expr) {
                continue;
            }
            if (!$this->phpAttributeAnalyzer->hasPhpAttribute($property, ClassName::JMS_TYPE)) {
                continue;
            }
            // this will be most likely collection, not single type
            if ($this->phpAttributeAnalyzer->hasPhpAttributes($property, array_merge(CollectionMapping::TO_MANY_CLASSES, CollectionMapping::TO_ONE_CLASSES))) {
                continue;
            }
            if (!$classReflection instanceof ClassReflection) {
                $classReflection = $this->reflectionResolver->resolveClassReflection($node);
            }
            if (!$classReflection instanceof ClassReflection) {
                return null;
            }
            if (!$this->makePropertyTypedGuard->isLegal($property, $classReflection, \true)) {
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
            $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeValue);
            if ($type instanceof MixedType) {
                // fallback to object type
                $type = new ObjectType($typeValue);
            }
            $propertyType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
            if (!$propertyType instanceof Identifier && !$propertyType instanceof FullyQualified) {
                return null;
            }
            $property->type = new NullableType($propertyType);
            $property->props[0]->default = new ConstFetch(new Name('null'));
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function resolveAttributeType(Property $property): ?string
    {
        foreach ($property->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if (!$this->isName($attr->name, ClassName::JMS_TYPE)) {
                    continue;
                }
                $typeValue = $this->valueResolver->getValue($attr->args[0]->value);
                if (!is_string($typeValue)) {
                    return null;
                }
                if (StringUtils::isMatch($typeValue, '#DateTime\<(.*?)\>#')) {
                    // special case for DateTime, which is not a scalar type
                    return 'DateTime';
                }
                return $typeValue;
            }
        }
        return null;
    }
}
