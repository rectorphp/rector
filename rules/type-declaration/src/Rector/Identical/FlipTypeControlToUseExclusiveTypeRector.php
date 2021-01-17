<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\FlipTypeControlToUseExclusiveTypeRectorTest
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
        return [Identical::class];
    }

    /**
     * @param Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNull($node->left) && ! $this->isNull($node->right)) {
            return null;
        }

        $variable = $this->isNull($node->left)
            ? $node->right
            : $node->left;

        /** @var Assign|null $assign */
        $assign = $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use ($variable): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            return $this->areNodesEqual($node->var, $variable);
        });

        if (! $assign instanceof Assign) {
            return null;
        }

        $expression = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if (! $expression instanceof Expression) {
            return null;
        }

        $phpDocInfo = $expression->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        /** @var VarTagValueNode|null $tagValueNode */
        $tagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $tagValueNode instanceof VarTagValueNode) {
            return null;
        }

        if (! $tagValueNode->type instanceof AttributeAwareUnionTypeNode) {
            return null;
        }

        if (count($tagValueNode->type->types) > 2) {
            return null;
        }

        /** @var AttributeAwareIdentifierTypeNode[] $types */
        $types = $tagValueNode->type->types;
        foreach ($tagValueNode->type->types as $type) {
            if (! $type instanceof AttributeAwareIdentifierTypeNode) {
                return null;
            }
        }

        if ($types[0]->name === $types[1]->name) {
            return null;
        }

        if ($types[0]->name !== 'null' && $types[1]->name !== 'null') {
            return null;
        }

        $type = $types[0]->name === 'null'
            ? $types[1]->name
            : $types[0]->name;

        if (class_exists($type)) {
            return new Instanceof_($variable, new FullyQualified($type));
        }

        if (interface_exists($type)) {
            return new Instanceof_($variable, new FullyQualified($type));
        }

        return null;
    }
}
