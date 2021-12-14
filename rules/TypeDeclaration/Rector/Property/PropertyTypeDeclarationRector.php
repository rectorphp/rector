<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @deprecated Split to smaller specific rules.
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\PropertyTypeDeclarationRectorTest
 */
final class PropertyTypeDeclarationRector extends AbstractRector
{
    public function __construct(
        private readonly PropertyTypeInferer $propertyTypeInferer,
        private readonly PhpDocTypeChanger $phpDocTypeChanger
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @var to properties that are missing it', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $value;

    public function run()
    {
        $this->value = 123;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int
     */
    private $value;

    public function run()
    {
        $this->value = 123;
    }
}
CODE_SAMPLE
            ),
        ]);
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
        if (count($node->props) !== 1) {
            return null;
        }

        if ($node->type !== null) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        if ($this->isVarDocAlreadySet($phpDocInfo)) {
            return null;
        }

        $type = $this->propertyTypeInferer->inferProperty($node);
        if ($type instanceof MixedType) {
            return null;
        }

        if (! $node->isPrivate() && $type instanceof NullType) {
            return null;
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return $this->completeTypedProperty($type, $node, $phpDocInfo);
        }

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);

        return $node;
    }

    private function isVarDocAlreadySet(PhpDocInfo $phpDocInfo): bool
    {
        foreach (['@var', '@phpstan-var', '@psalm-var'] as $tagName) {
            $varType = $phpDocInfo->getVarType($tagName);
            if (! $varType instanceof MixedType) {
                return true;
            }
        }

        return false;
    }

    private function completeTypedProperty(Type $type, Property $property, PhpDocInfo $phpDocInfo): Property
    {
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY());
        if ($propertyTypeNode instanceof UnionType) {
            if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
                $property->type = $propertyTypeNode;
                return $property;
            }

            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
            return $property;
        }

        $property->type = $propertyTypeNode;
        return $property;
    }
}
