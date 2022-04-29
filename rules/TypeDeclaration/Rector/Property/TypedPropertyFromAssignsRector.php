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
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\Php74\Guard\MakePropertyTypedGuard;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\AllAssignNodePropertyTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\TypedPropertyFromAssignsRectorTest
 */
final class TypedPropertyFromAssignsRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface
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
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
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
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\AllAssignNodePropertyTypeInferer $allAssignNodePropertyTypeInferer, \Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator $propertyTypeDecorator, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover $varTagRemover, \Rector\Php74\Guard\MakePropertyTypedGuard $makePropertyTypedGuard, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->allAssignNodePropertyTypeInferer = $allAssignNodePropertyTypeInferer;
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->varTagRemover = $varTagRemover;
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function configure(array $configuration) : void
    {
        $this->inlinePublic = $configuration[self::INLINE_PUBLIC] ?? (bool) \current($configuration);
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add typed property from assigned types', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        $inferredType = $this->allAssignNodePropertyTypeInferer->inferProperty($node);
        if (!$inferredType instanceof \PHPStan\Type\Type) {
            return null;
        }
        if ($inferredType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        $inferredType = $this->decorateTypeWithNullableIfDefaultPropertyNull($node, $inferredType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferredType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
        if ($typeNode === null) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $inferredType);
            return $node;
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES)) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $inferredType);
            return $node;
        }
        // non-private property can be anything with not inline public configured
        if (!$node->isPrivate() && !$this->inlinePublic) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $inferredType);
            return $node;
        }
        if ($inferredType instanceof \PHPStan\Type\UnionType) {
            $this->propertyTypeDecorator->decoratePropertyUnionType($inferredType, $typeNode, $node, $phpDocInfo);
        } else {
            $node->type = $typeNode;
        }
        $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $node);
        return $node;
    }
    private function decorateTypeWithNullableIfDefaultPropertyNull(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $inferredType) : \PHPStan\Type\Type
    {
        $defaultExpr = $property->props[0]->default;
        if (!$defaultExpr instanceof \PhpParser\Node\Expr) {
            return $inferredType;
        }
        if (!$this->valueResolver->isNull($defaultExpr)) {
            return $inferredType;
        }
        if (\PHPStan\Type\TypeCombinator::containsNull($inferredType)) {
            return $inferredType;
        }
        return \PHPStan\Type\TypeCombinator::addNull($inferredType);
    }
}
