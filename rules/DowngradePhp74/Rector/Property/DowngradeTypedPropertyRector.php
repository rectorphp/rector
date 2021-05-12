<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\DowngradeTypedPropertyRectorTest
 */
final class DowngradeTypedPropertyRector extends AbstractRector
{
    public function __construct(
        private PhpDocTypeChanger $phpDocTypeChanger
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes property type definition from type definitions to `@var` annotations.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private string $property;
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
    * @var string
    */
    private $property;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->type === null) {
            return null;
        }

        if ($node->type instanceof NullableType) {
            $node->props[0]->default = null;
        }

        $this->decoratePropertyWithDocBlock($node, $node->type);
        $node->type = null;

        return $node;
    }

    private function decoratePropertyWithDocBlock(Property $property, Node $typeNode): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return;
        }

        $newType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $newType);
    }
}
