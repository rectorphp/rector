<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\Php74\Guard\MakePropertyTypedGuard;
use Rector\Php74\TypeAnalyzer\ObjectTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer;
use Rector\VendorLocker\VendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/typed_properties_v2#proposal
 *
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ImportedTest
 */
final class TypedPropertyRector extends AbstractScopeAwareRector implements AllowEmptyConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @api
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
    public function __construct(VarDocPropertyTypeInferer $varDocPropertyTypeInferer, VendorLockResolver $vendorLockResolver, VarTagRemover $varTagRemover, FamilyRelationsAnalyzer $familyRelationsAnalyzer, ObjectTypeAnalyzer $objectTypeAnalyzer, MakePropertyTypedGuard $makePropertyTypedGuard, ConstructorAssignDetector $constructorAssignDetector)
    {
        $this->varDocPropertyTypeInferer = $varDocPropertyTypeInferer;
        $this->vendorLockResolver = $vendorLockResolver;
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
        $resolvedPropertyType = $this->varDocPropertyTypeInferer->inferProperty($node);
        if ($resolvedPropertyType instanceof MixedType) {
            return null;
        }
        if ($this->objectTypeAnalyzer->isSpecial($resolvedPropertyType)) {
            return null;
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($resolvedPropertyType, TypeKind::PROPERTY);
        if ($this->isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($propertyTypeNode, $node)) {
            return null;
        }
        $propertyType = $this->familyRelationsAnalyzer->getPossibleUnionPropertyType($node, $resolvedPropertyType, $scope, $propertyTypeNode);
        $varDocType = $propertyType->getVarType();
        $propertyTypeNode = $propertyType->getPropertyTypeNode();
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($node, $varDocType);
        $this->addDefaultValueNullForNullableType($node, $varDocType);
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
    private function addDefaultValueNullForNullableType(Property $property, Type $propertyType) : void
    {
        if (!TypeCombinator::containsNull($propertyType)) {
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
