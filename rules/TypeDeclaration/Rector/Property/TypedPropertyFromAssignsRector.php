<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\Php74\Guard\MakePropertyTypedGuard;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\AllAssignNodePropertyTypeInferer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\TypedPropertyFromAssignsRectorTest
 */
final class TypedPropertyFromAssignsRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\AllAssignNodePropertyTypeInferer
     */
    private $allAssignNodePropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\Php74\Guard\MakePropertyTypedGuard
     */
    private $makePropertyTypedGuard;
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
    public function __construct(AllAssignNodePropertyTypeInferer $allAssignNodePropertyTypeInferer, PropertyTypeDecorator $propertyTypeDecorator, VarTagRemover $varTagRemover, MakePropertyTypedGuard $makePropertyTypedGuard)
    {
        $this->allAssignNodePropertyTypeInferer = $allAssignNodePropertyTypeInferer;
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->varTagRemover = $varTagRemover;
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
    }
    public function configure(array $configuration) : void
    {
        $this->inlinePublic = $configuration[self::INLINE_PUBLIC] ?? (bool) \current($configuration);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add typed property from assigned types', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $name;

    public function run()
    {
        $this->name = 'string';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private string|null $name = null;

    public function run()
    {
        $this->name = 'string';
    }
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->makePropertyTypedGuard->isLegal($node, $this->inlinePublic)) {
            return null;
        }
        // non-private property can be anything with not inline public configured
        if (!$node->isPrivate() && !$this->inlinePublic) {
            return null;
        }
        $inferredType = $this->allAssignNodePropertyTypeInferer->inferProperty($node);
        if (!$inferredType instanceof Type) {
            return null;
        }
        if ($inferredType instanceof MixedType) {
            return null;
        }
        $inferredType = $this->decorateTypeWithNullableIfDefaultPropertyNull($node, $inferredType);
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferredType, TypeKind::PROPERTY);
        if ($typeNode === null) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($inferredType instanceof UnionType) {
            $this->propertyTypeDecorator->decoratePropertyUnionType($inferredType, $typeNode, $node, $phpDocInfo, \false);
        } else {
            $node->type = $typeNode;
        }
        if (!$node->type instanceof Node) {
            return null;
        }
        $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $node);
        return $node;
    }
    private function decorateTypeWithNullableIfDefaultPropertyNull(Property $property, Type $inferredType) : Type
    {
        $defaultExpr = $property->props[0]->default;
        if (!$defaultExpr instanceof Expr) {
            return $inferredType;
        }
        if (!$this->valueResolver->isNull($defaultExpr)) {
            return $inferredType;
        }
        if (TypeCombinator::containsNull($inferredType)) {
            return $inferredType;
        }
        return TypeCombinator::addNull($inferredType);
    }
}
