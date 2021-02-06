<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadDocBlock\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\ConstructorPropertyTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\TypedPropertyFromStrictConstructorRectorTest
 */
final class TypedPropertyFromStrictConstructorRector extends AbstractRector
{
    /**
     * @var ConstructorPropertyTypeInferer
     */
    private $constructorPropertyTypeInferer;

    /**
     * @var VarTagRemover
     */
    private $varTagRemover;

    public function __construct(
        ConstructorPropertyTypeInferer $constructorPropertyTypeInferer,
        VarTagRemover $varTagRemover
    ) {
        $this->constructorPropertyTypeInferer = $constructorPropertyTypeInferer;
        $this->varTagRemover = $varTagRemover;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add typed properties based only on strict constructor types', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeObject
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }

        if ($node->type !== null) {
            return null;
        }

        $varType = $this->constructorPropertyTypeInferer->inferProperty($node);
        if ($varType instanceof MixedType) {
            return null;
        }

        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $varType,
            PHPStanStaticTypeMapper::KIND_PROPERTY
        );

        if ($propertyTypeNode === null) {
            return null;
        }

        $node->type = $propertyTypeNode;
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($node, $varType);

        return $node;
    }
}
