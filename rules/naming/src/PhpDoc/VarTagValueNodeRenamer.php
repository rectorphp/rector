<?php

declare(strict_types=1);

namespace Rector\Naming\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VarTagValueNodeRenamer
{
    public function renameAssignVarTagVariableName(Node $node, string $originalName, string $expectedName): void
    {
        $phpDocInfo = $this->resolvePhpDocInfo($node);
        if ($phpDocInfo === null) {
            return;
        }

        $varTagValueNode = $phpDocInfo->getByType(VarTagValueNode::class);
        if ($varTagValueNode === null) {
            return;
        }

        if ($varTagValueNode->variableName !== '$' . $originalName) {
            return;
        }

        $varTagValueNode->variableName = '$' . $expectedName;
    }

    /**
     * Expression doc block has higher priority
     */
    private function resolvePhpDocInfo(Node $node): ?PhpDocInfo
    {
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $expression = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($expression instanceof Node) {
            $expressionPhpDocInfo = $expression->getAttribute(AttributeKey::PHP_DOC_INFO);
        }

        return $expressionPhpDocInfo ?? $phpDocInfo;
    }
}
