<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Privatization\Guard\ParentPropertyLookupGuard;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\GetterTypeDeclarationPropertyTypeInferer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\TypedPropertyFromStrictGetterMethodReturnTypeRectorTest
 */
final class TypedPropertyFromStrictGetterMethodReturnTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\GetterTypeDeclarationPropertyTypeInferer
     */
    private $getterTypeDeclarationPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\Privatization\Guard\ParentPropertyLookupGuard
     */
    private $parentPropertyLookupGuard;
    public function __construct(GetterTypeDeclarationPropertyTypeInferer $getterTypeDeclarationPropertyTypeInferer, VarTagRemover $varTagRemover, ParentPropertyLookupGuard $parentPropertyLookupGuard)
    {
        $this->getterTypeDeclarationPropertyTypeInferer = $getterTypeDeclarationPropertyTypeInferer;
        $this->varTagRemover = $varTagRemover;
        $this->parentPropertyLookupGuard = $parentPropertyLookupGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Complete property type based on getter strict types', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public $name;

    public function getName(): string|null
    {
        return $this->name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public ?string $name = null;

    public function getName(): string|null
    {
        return $this->name;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Stmt\Class_
    {
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if ($this->shouldSkipProperty($property, $node)) {
                continue;
            }
            $getterReturnType = $this->getterTypeDeclarationPropertyTypeInferer->inferProperty($property);
            if (!$getterReturnType instanceof Type) {
                continue;
            }
            if ($getterReturnType instanceof MixedType) {
                continue;
            }
            // if property is public, it should be nullable
            if ($property->isPublic() && !TypeCombinator::containsNull($getterReturnType)) {
                $getterReturnType = TypeCombinator::addNull($getterReturnType);
            }
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($getterReturnType, TypeKind::PROPERTY);
            if (!$propertyTypeNode instanceof Node) {
                continue;
            }
            // include fault value in the type
            if ($this->isConflictingDefaultExprType($property, $getterReturnType)) {
                continue;
            }
            $property->type = $propertyTypeNode;
            $this->decorateDefaultNull($getterReturnType, $property);
            $this->refactorPhpDoc($property);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function decorateDefaultNull(Type $propertyType, Property $property) : void
    {
        if (!TypeCombinator::containsNull($propertyType)) {
            return;
        }
        $propertyProperty = $property->props[0];
        if ($propertyProperty->default instanceof Expr) {
            return;
        }
        $propertyProperty->default = $this->nodeFactory->createNull();
    }
    private function isConflictingDefaultExprType(Property $property, Type $getterReturnType) : bool
    {
        $onlyPropertyProperty = $property->props[0];
        if (!$onlyPropertyProperty->default instanceof Expr) {
            return \false;
        }
        $defaultType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($onlyPropertyProperty->default);
        // does default type match the getter one?
        return !$defaultType->isSuperTypeOf($getterReturnType)->yes();
    }
    private function refactorPhpDoc(Property $property) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $property);
    }
    private function shouldSkipProperty(Property $property, Class_ $class) : bool
    {
        if ($property->type instanceof Node) {
            // already has type
            return \true;
        }
        // skip non-single property
        if (\count($property->props) !== 1) {
            // has too many properties
            return \true;
        }
        return !$this->parentPropertyLookupGuard->isLegal($property, $class);
    }
}
