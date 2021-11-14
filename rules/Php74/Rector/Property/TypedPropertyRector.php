<?php

declare (strict_types=1);
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
use Rector\Php74\TypeAnalyzer\PropertyUnionTypeResolver;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
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
final class TypedPropertyRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface, \Rector\VersionBonding\Contract\MinPhpVersionInterface
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
     * @var bool
     */
    private $classLikeTypeOnly = \false;
    /**
     * If want to keep BC, it can be set to true
     * @see https://3v4l.org/spl4P
     * @var bool
     */
    private $privatePropertyOnly = \false;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer
     */
    private $propertyTypeInferer;
    /**
     * @var \Rector\VendorLocker\VendorLockResolver
     */
    private $vendorLockResolver;
    /**
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @var \Rector\Core\NodeAnalyzer\PropertyAnalyzer
     */
    private $propertyAnalyzer;
    /**
     * @var \Rector\Php74\TypeAnalyzer\PropertyUnionTypeResolver
     */
    private $propertyUnionTypeResolver;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer $propertyTypeInferer, \Rector\VendorLocker\VendorLockResolver $vendorLockResolver, \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer $doctrineTypeAnalyzer, \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover $varTagRemover, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer, \Rector\Core\NodeAnalyzer\PropertyAnalyzer $propertyAnalyzer, \Rector\Php74\TypeAnalyzer\PropertyUnionTypeResolver $propertyUnionTypeResolver, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->propertyTypeInferer = $propertyTypeInferer;
        $this->vendorLockResolver = $vendorLockResolver;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->varTagRemover = $varTagRemover;
        $this->reflectionProvider = $reflectionProvider;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->propertyAnalyzer = $propertyAnalyzer;
        $this->propertyUnionTypeResolver = $propertyUnionTypeResolver;
        $this->astResolver = $astResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes property `@var` annotations from annotation to type.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var int
     */
    private $count;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $count;
}
CODE_SAMPLE
, [self::CLASS_LIKE_TYPE_ONLY => \false, self::PRIVATE_PROPERTY_ONLY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipProperty($node)) {
            return null;
        }
        $varType = $this->propertyTypeInferer->inferProperty($node);
        if ($varType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        if ($varType instanceof \PHPStan\Type\UnionType) {
            $types = $varType->getTypes();
            if (\count($types) === 2 && $types[1] instanceof \PHPStan\Type\Generic\TemplateType) {
                $templateType = $types[1];
                $node->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($templateType->getBound(), \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
                return $node;
            }
        }
        // we are not sure what object type this is
        if ($varType instanceof \Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType) {
            return null;
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
        if ($this->isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($propertyTypeNode, $node, $varType)) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $propertyType = $this->familyRelationsAnalyzer->getPossibleUnionPropertyType($node, $varType, $scope, $propertyTypeNode);
        $varType = $propertyType->getVarType();
        $propertyTypeNode = $propertyType->getPropertyTypeNode();
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($node, $varType);
        $this->removeDefaultValueForDoctrineCollection($node, $varType);
        $this->addDefaultValueNullForNullableType($node, $varType);
        $node->type = $propertyTypeNode;
        return $node;
    }
    /**
     * @param array<string, bool> $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->classLikeTypeOnly = $configuration[self::CLASS_LIKE_TYPE_ONLY] ?? \false;
        $this->privatePropertyOnly = $configuration[self::PRIVATE_PROPERTY_ONLY] ?? \false;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Name|null $node
     */
    private function isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($node, \PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $type) : bool
    {
        if (!$node instanceof \PhpParser\Node) {
            return \true;
        }
        $type = $this->propertyUnionTypeResolver->resolve($node, $type);
        // is not class-type and should be skipped
        if ($this->shouldSkipNonClassLikeType($node, $type)) {
            return \true;
        }
        // false positive
        if (!$node instanceof \PhpParser\Node\Name) {
            return $this->vendorLockResolver->isPropertyTypeChangeVendorLockedIn($property);
        }
        if (!$this->isName($node, 'mixed')) {
            return $this->vendorLockResolver->isPropertyTypeChangeVendorLockedIn($property);
        }
        return \true;
    }
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Name $node
     */
    private function shouldSkipNonClassLikeType($node, \PHPStan\Type\Type $type) : bool
    {
        // unwrap nullable type
        if ($node instanceof \PhpParser\Node\NullableType) {
            $node = $node->type;
        }
        $typeName = $this->getName($node);
        if ($typeName === null) {
            return \false;
        }
        if (!$this->classLikeTypeOnly) {
            return \false;
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
            $typeName = $type->getFullyQualifiedName();
        }
        return !$this->reflectionProvider->hasClass($typeName);
    }
    private function removeDefaultValueForDoctrineCollection(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $propertyType) : void
    {
        if (!$this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($propertyType)) {
            return;
        }
        $onlyProperty = $property->props[0];
        $onlyProperty->default = null;
    }
    private function addDefaultValueNullForNullableType(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $propertyType) : void
    {
        if (!$propertyType instanceof \PHPStan\Type\UnionType) {
            return;
        }
        if (!$propertyType->isSuperTypeOf(new \PHPStan\Type\NullType())->yes()) {
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
    private function shouldSkipProperty(\PhpParser\Node\Stmt\Property $property) : bool
    {
        // type is already set → skip
        if ($property->type !== null) {
            return \true;
        }
        // skip multiple properties
        if (\count($property->props) > 1) {
            return \true;
        }
        $trait = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\Trait_::class);
        // skip trait properties, as they ar unpredictable based on class context they appear in
        if ($trait instanceof \PhpParser\Node\Stmt\Trait_) {
            return \true;
        }
        $propertyName = $this->getName($property);
        $classLike = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\ClassLike::class);
        if ($classLike instanceof \PhpParser\Node\Stmt\ClassLike && $this->isModifiedByTrait($classLike, $propertyName)) {
            return \true;
        }
        if (!$this->privatePropertyOnly) {
            return $this->propertyAnalyzer->hasForbiddenType($property);
        }
        if ($property->isPrivate()) {
            return $this->propertyAnalyzer->hasForbiddenType($property);
        }
        return \true;
    }
    private function isModifiedByTrait(\PhpParser\Node\Stmt\ClassLike $classLike, string $propertyName) : bool
    {
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        foreach ($classLike->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $traitName) {
                $trait = $this->astResolver->resolveClassFromName($traitName->toString());
                if (!$trait instanceof \PhpParser\Node\Stmt\Trait_) {
                    continue;
                }
                if ($this->propertyFetchAnalyzer->containsLocalPropertyFetchName($trait, $propertyName)) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
