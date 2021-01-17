<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\Assign\FlipTypeControlToUseExclusiveTypeRector\FlipTypeControlToUseExclusiveTypeRectorTest
 */
final class FlipTypeControlToUseExclusiveTypeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Flip type control to use exclusive type',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(array $values)
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(array $values)
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo instanceof PhpDocInfo) {
            return;
        }
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        $expression = $node->getAttribute(Attributekey::PARENT_NODE);
        $phpDocInfo = $expression->getAttribute(AttributeKey::PHP_DOC_INFO);

        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        $tagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $tagValueNode->type instanceof AttributeAwareUnionTypeNode) {
            return null;
        }

        if (count($tagValueNode->type->types) !== 2) {
            return null;
        }

        if ($tagValueNode->type->types[0]->name === $tagValueNode->type->types[1]->name) {
            return null;
        }

        if ($tagValueNode->type->types[0]->name !== 'null' && $tagValueNode->type->types[1]->name !== 'null') {
            return null;
        }

        return $node;
    }
}
