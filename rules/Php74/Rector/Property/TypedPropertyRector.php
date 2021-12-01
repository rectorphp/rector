<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\PropertyAnalyzer;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php74\TypeAnalyzer\ObjectTypeAnalyzer;
use Rector\Php74\TypeAnalyzer\PropertyUnionTypeResolver;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;
use Rector\VendorLocker\VendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/typed_properties_v2#proposal
 *
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ClassLikeTypesOnlyTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\DoctrineTypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ImportedTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\UnionTypedPropertyRectorTest
 */
final class TypedPropertyRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var string
     */
    public const CLASS_LIKE_TYPE_ONLY = 'class_like_type_only';

    /**
     * @var string
     */
    public const PRIVATE_PROPERTY_ONLY = 'PRIVATE_PROPERTY_ONLY';

    /**
     * Useful for refactoring of huge applications. Taking types first narrows scope
     */
    private bool $classLikeTypeOnly = false;

    /**
     * If want to keep BC, it can be set to true
     * @see https://3v4l.org/spl4P
     */
    private bool $privatePropertyOnly = false;

    public function __construct(
        private PropertyTypeInferer $propertyTypeInferer,
        private VendorLockResolver $vendorLockResolver,
        private DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
        private VarTagRemover $varTagRemover,
        private ReflectionProvider $reflectionProvider,
        private PropertyFetchAnalyzer $propertyFetchAnalyzer,
        private FamilyRelationsAnalyzer $familyRelationsAnalyzer,
        private PropertyAnalyzer $propertyAnalyzer,
        private PropertyUnionTypeResolver $propertyUnionTypeResolver,
        private AstResolver $astResolver,
        private ObjectTypeAnalyzer $objectTypeAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes property `@var` annotations from annotation to type.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var int
     */
    private $count;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $count;
}
CODE_SAMPLE
                    ,
                    [
                        self::CLASS_LIKE_TYPE_ONLY => false,
                        self::PRIVATE_PROPERTY_ONLY => false,
                    ]
                ),
            ]
        );
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
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        $varType = $this->propertyTypeInferer->inferProperty($node);
        if ($varType instanceof MixedType) {
            return null;
        }

        if ($varType instanceof UnionType) {
            $types = $varType->getTypes();

            if (count($types) === 2 && $types[1] instanceof TemplateType) {
                $templateType = $types[1];

                $node->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
                    $templateType->getBound(),
                    TypeKind::PROPERTY()
                );

                return $node;
            }
        }

        if ($this->objectTypeAnalyzer->isSpecial($varType)) {
            return null;
        }

        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, TypeKind::PROPERTY());

        if ($this->isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($propertyTypeNode, $node, $varType)) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);

        $propertyType = $this->familyRelationsAnalyzer->getPossibleUnionPropertyType(
            $node,
            $varType,
            $scope,
            $propertyTypeNode
        );

        $varType = $propertyType->getVarType();
        $propertyTypeNode = $propertyType->getPropertyTypeNode();

        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($node, $varType);
        $this->removeDefaultValueForDoctrineCollection($node, $varType);
        $this->addDefaultValueNullForNullableType($node, $varType);

        $node->type = $propertyTypeNode;

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->classLikeTypeOnly = $configuration[self::CLASS_LIKE_TYPE_ONLY] ?? false;
        $this->privatePropertyOnly = $configuration[self::PRIVATE_PROPERTY_ONLY] ?? false;
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }

    private function isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn(
        Name | ComplexType | null $node,
        Property $property,
        Type $type
    ): bool {
        if (! $node instanceof Node) {
            return true;
        }

        $type = $this->propertyUnionTypeResolver->resolve($node, $type);

        // is not class-type and should be skipped
        if ($this->shouldSkipNonClassLikeType($node, $type)) {
            return true;
        }

        // false positive
        if (! $node instanceof Name) {
            return $this->vendorLockResolver->isPropertyTypeChangeVendorLockedIn($property);
        }

        if (! $this->isName($node, 'mixed')) {
            return $this->vendorLockResolver->isPropertyTypeChangeVendorLockedIn($property);
        }

        return true;
    }

    private function shouldSkipNonClassLikeType(Name|ComplexType $node, Type $type): bool
    {
        // unwrap nullable type
        if ($node instanceof NullableType) {
            $node = $node->type;
        }

        $typeName = $this->getName($node);
        if ($typeName === null) {
            return false;
        }

        if (! $this->classLikeTypeOnly) {
            return false;
        }

        if ($type instanceof AliasedObjectType) {
            $typeName = $type->getFullyQualifiedName();
        }

        return ! $this->reflectionProvider->hasClass($typeName);
    }

    private function removeDefaultValueForDoctrineCollection(Property $property, Type $propertyType): void
    {
        if (! $this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($propertyType)) {
            return;
        }

        $onlyProperty = $property->props[0];
        $onlyProperty->default = null;
    }

    private function addDefaultValueNullForNullableType(Property $property, Type $propertyType): void
    {
        if (! $propertyType instanceof UnionType) {
            return;
        }

        if (! $propertyType->isSuperTypeOf(new NullType())->yes()) {
            return;
        }

        $onlyProperty = $property->props[0];

        // skip is already has value
        if ($onlyProperty->default !== null) {
            return;
        }

        if ($this->propertyFetchAnalyzer->isFilledByConstructParam($property)) {
            return;
        }

        $onlyProperty->default = $this->nodeFactory->createNull();
    }

    private function shouldSkipProperty(Property $property): bool
    {
        // type is already set → skip
        if ($property->type !== null) {
            return true;
        }

        // skip multiple properties
        if (count($property->props) > 1) {
            return true;
        }

        $trait = $this->betterNodeFinder->findParentType($property, Trait_::class);
        // skip trait properties, as they ar unpredictable based on class context they appear in
        if ($trait instanceof Trait_) {
            return true;
        }

        $propertyName = $this->getName($property);

        $classLike = $this->betterNodeFinder->findParentType($property, ClassLike::class);
        if ($classLike instanceof ClassLike && $this->isModifiedByTrait($classLike, $propertyName)) {
            return true;
        }

        if (! $this->privatePropertyOnly) {
            return $this->propertyAnalyzer->hasForbiddenType($property);
        }

        if ($property->isPrivate()) {
            return $this->propertyAnalyzer->hasForbiddenType($property);
        }

        return true;
    }

    private function isModifiedByTrait(ClassLike $classLike, string $propertyName): bool
    {
        if (! $classLike instanceof Class_) {
            return false;
        }

        foreach ($classLike->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $traitName) {
                $trait = $this->astResolver->resolveClassFromName($traitName->toString());
                if (! $trait instanceof Trait_) {
                    continue;
                }

                if ($this->propertyFetchAnalyzer->containsLocalPropertyFetchName($trait, $propertyName)) {
                    return true;
                }
            }
        }

        return false;
    }
}
