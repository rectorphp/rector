<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\NodeAnalyzer\PropertyAnalyzer;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php74\TypeAnalyzer\ObjectTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer;
use Rector\VendorLocker\VendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/typed_properties_v2#proposal
 *
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ClassLikeTypesOnlyTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\DoctrineTypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ImportedTest
 */
final class TypedPropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    public function __construct(
        private readonly VarDocPropertyTypeInferer $varDocPropertyTypeInferer,
        private readonly VendorLockResolver $vendorLockResolver,
        private readonly DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
        private readonly VarTagRemover $varTagRemover,
        private readonly PropertyFetchAnalyzer $propertyFetchAnalyzer,
        private readonly FamilyRelationsAnalyzer $familyRelationsAnalyzer,
        private readonly PropertyAnalyzer $propertyAnalyzer,
        private readonly AstResolver $astResolver,
        private readonly ObjectTypeAnalyzer $objectTypeAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes property `@var` annotations from annotation to type.',
            [
                new CodeSample(
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
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($this->shouldSkipProperty($node, $scope)) {
            return null;
        }

        $varType = $this->varDocPropertyTypeInferer->inferProperty($node);
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

        if ($this->isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($propertyTypeNode, $node)) {
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

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }

    private function isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn(
        Name | ComplexType | null $node,
        Property $property,
    ): bool {
        if (! $node instanceof Node) {
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

    private function shouldSkipProperty(Property $property, Scope $scope): bool
    {
        // type is already set → skip
        if ($property->type !== null) {
            return true;
        }

        // skip multiple properties
        if (count($property->props) > 1) {
            return true;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        /**
         * - skip trait properties, as they are unpredictable based on class context they appear in
         * - skip interface properties as well, as interface not allowed to have property
         */
        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (! $class instanceof Class_) {
            return true;
        }

        $propertyName = $this->getName($property);

        if ($this->isModifiedByTrait($class, $propertyName)) {
            return true;
        }

        if ($property->isPrivate()) {
            return $this->propertyAnalyzer->hasForbiddenType($property);
        }

        // is we're in final class, the type can be changed
        return ! ($this->isSafeProtectedProperty($property, $class));
    }

    private function isModifiedByTrait(Class_ $class, string $propertyName): bool
    {
        foreach ($class->getTraitUses() as $traitUse) {
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

    private function isSafeProtectedProperty(Property $property, Class_ $class): bool
    {
        if (! $property->isProtected()) {
            return false;
        }

        if (! $class->isFinal()) {
            return false;
        }

        return ! $class->extends instanceof FullyQualified;
    }
}
