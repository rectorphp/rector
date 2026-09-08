<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\NodeManipulator\ColumnPropertyTypeResolver;
use Rector\Doctrine\NodeManipulator\NullabilityColumnPropertyTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\TypedPropertyFromColumnTypeRectorTest
 */
final class TypedPropertyFromColumnTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private PropertyTypeDecorator $propertyTypeDecorator;
    /**
     * @readonly
     */
    private ColumnPropertyTypeResolver $columnPropertyTypeResolver;
    /**
     * @readonly
     */
    private NullabilityColumnPropertyTypeResolver $nullabilityColumnPropertyTypeResolver;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(PropertyTypeDecorator $propertyTypeDecorator, ColumnPropertyTypeResolver $columnPropertyTypeResolver, NullabilityColumnPropertyTypeResolver $nullabilityColumnPropertyTypeResolver, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, ReflectionResolver $reflectionResolver)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->columnPropertyTypeResolver = $columnPropertyTypeResolver;
        $this->nullabilityColumnPropertyTypeResolver = $nullabilityColumnPropertyTypeResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Complete @var annotations or types based on @ORM\Column', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\Column(type="string")
     */
    private $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\Column(type="string")
     */
    private string|null $name = null;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Stmt\Property
    {
        if ($node->type !== null) {
            return null;
        }
        // avoid untyped parent property override, that would be a fatal error
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if ($classReflection instanceof ClassReflection && $this->hasUntypedParentProperty($classReflection, $node)) {
            return null;
        }
        $isNullable = $this->nullabilityColumnPropertyTypeResolver->isNullable($node);
        $propertyType = $this->columnPropertyTypeResolver->resolve($node, $isNullable);
        if (!$propertyType instanceof Type || $propertyType instanceof MixedType) {
            return null;
        }
        // add default null if missing
        if ($isNullable && !TypeCombinator::containsNull($propertyType)) {
            $propertyType = TypeCombinator::addNull($propertyType);
        }
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
        if (!$typeNode instanceof Node) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        // Doctrine "decimal"/"bigint" map to string; keep a numeric default valid as string, eg: 0 to '0'
        $this->refactorNumericDefaultToString($node, $propertyType);
        if ($propertyType instanceof UnionType) {
            $this->propertyTypeDecorator->decoratePropertyUnionType($propertyType, $typeNode, $node, $phpDocInfo);
            return $node;
        }
        $node->type = $typeNode;
        return $node;
    }
    private function refactorNumericDefaultToString(Property $property, Type $propertyType): void
    {
        $bareType = TypeCombinator::removeNull($propertyType);
        if (!$bareType instanceof StringType) {
            return;
        }
        $propertyItem = $property->props[0];
        $default = $propertyItem->default;
        if (!$default instanceof Int_ && !$default instanceof Float_) {
            return;
        }
        $propertyItem->default = new String_((string) $default->value);
    }
    private function hasUntypedParentProperty(ClassReflection $classReflection, Property $property): bool
    {
        $propertyName = $this->getName($property);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $nativeReflectionClass = $parentClassReflection->getNativeReflection();
            if (!$nativeReflectionClass->hasProperty($propertyName)) {
                continue;
            }
            // typing the child while the parent property stays untyped is a fatal error
            return $nativeReflectionClass->getProperty($propertyName)->getType() === null;
        }
        return \false;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
