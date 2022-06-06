<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php74\Rector\Property;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use RectorPrefix20220606\Rector\Core\Rector\AbstractScopeAwareRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use RectorPrefix20220606\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use RectorPrefix20220606\Rector\Php74\Guard\MakePropertyTypedGuard;
use RectorPrefix20220606\Rector\Php74\TypeAnalyzer\ObjectTypeAnalyzer;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer;
use RectorPrefix20220606\Rector\VendorLocker\VendorLockResolver;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/typed_properties_v2#proposal
 *
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ClassLikeTypesOnlyTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\DoctrineTypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ImportedTest
 */
final class TypedPropertyRector extends AbstractScopeAwareRector implements AllowEmptyConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var string
     */
    public const INLINE_PUBLIC = 'inline_public';
    /**
     * Default to false, which only apply changes:
     *
     *  – private modifier property
     *  - protected modifier property on final class without extends or has extends but property and/or its usage only in current class
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
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    public function __construct(VarDocPropertyTypeInferer $varDocPropertyTypeInferer, VendorLockResolver $vendorLockResolver, DoctrineTypeAnalyzer $doctrineTypeAnalyzer, VarTagRemover $varTagRemover, FamilyRelationsAnalyzer $familyRelationsAnalyzer, ObjectTypeAnalyzer $objectTypeAnalyzer, MakePropertyTypedGuard $makePropertyTypedGuard, ConstructorAssignDetector $constructorAssignDetector)
    {
        $this->varDocPropertyTypeInferer = $varDocPropertyTypeInferer;
        $this->vendorLockResolver = $vendorLockResolver;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->varTagRemover = $varTagRemover;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->objectTypeAnalyzer = $objectTypeAnalyzer;
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
        $this->constructorAssignDetector = $constructorAssignDetector;
    }
    public function configure(array $configuration) : void
    {
        $this->inlinePublic = $configuration[self::INLINE_PUBLIC] ?? (bool) \current($configuration);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes property type by `@var` annotations or default value.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if (!$this->makePropertyTypedGuard->isLegal($node, $this->inlinePublic)) {
            return null;
        }
        $varType = $this->varDocPropertyTypeInferer->inferProperty($node);
        if ($varType instanceof MixedType) {
            return null;
        }
        if ($this->objectTypeAnalyzer->isSpecial($varType)) {
            return null;
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, TypeKind::PROPERTY);
        if ($this->isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($propertyTypeNode, $node)) {
            return null;
        }
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
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\ComplexType|null $node
     */
    private function isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($node, Property $property) : bool
    {
        if (!$node instanceof Node) {
            return \true;
        }
        if ($node instanceof NullableType && $this->isName($node->type, 'mixed')) {
            return \true;
        }
        // false positive
        if (!$node instanceof Name) {
            return $this->vendorLockResolver->isPropertyTypeChangeVendorLockedIn($property);
        }
        if ($this->isName($node, 'mixed')) {
            return \true;
        }
        return $this->vendorLockResolver->isPropertyTypeChangeVendorLockedIn($property);
    }
    private function removeDefaultValueForDoctrineCollection(Property $property, Type $propertyType) : void
    {
        if (!$this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($propertyType)) {
            return;
        }
        $onlyProperty = $property->props[0];
        $onlyProperty->default = null;
    }
    private function addDefaultValueNullForNullableType(Property $property, Type $propertyType) : void
    {
        if (!$propertyType instanceof UnionType) {
            return;
        }
        if (!$propertyType->isSuperTypeOf(new NullType())->yes()) {
            return;
        }
        $onlyProperty = $property->props[0];
        // skip is already has value
        if ($onlyProperty->default !== null) {
            return;
        }
        $classLike = $this->betterNodeFinder->findParentType($property, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName)) {
            return;
        }
        $onlyProperty->default = $this->nodeFactory->createNull();
    }
}
