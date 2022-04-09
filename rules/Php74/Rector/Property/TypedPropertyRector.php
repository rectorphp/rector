<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php74\Guard\MakePropertyTypedGuard;
use Rector\Php74\TypeAnalyzer\ObjectTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer;
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
 */
final class TypedPropertyRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface, \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    public const INLINE_PUBLIC = 'inline_public';
    /**
     * Default to false, which only apply changes:
     *
     *  – private modifier property
     *  - protected modifier property on final class without extends
     *
     * Set to true will allow change other modifiers as well as far as not forbidden, eg: callable type, null type, etc.
     * @var bool
     */
    private $inlinePublic = \false;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer
     */
    private $varDocPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\VendorLocker\VendorLockResolver
     */
    private $vendorLockResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php74\TypeAnalyzer\ObjectTypeAnalyzer
     */
    private $objectTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php74\Guard\MakePropertyTypedGuard
     */
    private $makePropertyTypedGuard;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer $varDocPropertyTypeInferer, \Rector\VendorLocker\VendorLockResolver $vendorLockResolver, \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer $doctrineTypeAnalyzer, \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover $varTagRemover, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer, \Rector\Php74\TypeAnalyzer\ObjectTypeAnalyzer $objectTypeAnalyzer, \Rector\Php74\Guard\MakePropertyTypedGuard $makePropertyTypedGuard)
    {
        $this->varDocPropertyTypeInferer = $varDocPropertyTypeInferer;
        $this->vendorLockResolver = $vendorLockResolver;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->varTagRemover = $varTagRemover;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->objectTypeAnalyzer = $objectTypeAnalyzer;
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
    }
    public function configure(array $configuration) : void
    {
        $this->inlinePublic = $configuration[self::INLINE_PUBLIC] ?? (bool) \current($configuration);
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes property type by `@var` annotations or default value.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var int
     */
    private $count;

    private $isDone = false;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $count;

    private bool $isDone = false;
}
CODE_SAMPLE
, [self::INLINE_PUBLIC => \false])]);
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
        if (!$this->makePropertyTypedGuard->isLegal($node, $this->inlinePublic)) {
            return null;
        }
        $varType = $this->varDocPropertyTypeInferer->inferProperty($node);
        if ($varType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        if ($this->objectTypeAnalyzer->isSpecial($varType)) {
            return null;
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
        if ($this->isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($propertyTypeNode, $node)) {
            return null;
        }
        /** @var Scope $scope */
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
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Name|null $node
     */
    private function isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($node, \PhpParser\Node\Stmt\Property $property) : bool
    {
        if (!$node instanceof \PhpParser\Node) {
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
}
