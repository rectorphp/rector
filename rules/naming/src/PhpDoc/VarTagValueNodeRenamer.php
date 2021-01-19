<?php

declare(strict_types=1);

namespace Rector\Naming\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VarTagValueNodeRenamer
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function renameAssignVarTagVariableName(Node $node, string $originalName, string $expectedName): void
    {
        $phpDocInfo = $this->resolvePhpDocInfo($node);

        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $varTagValueNode instanceof VarTagValueNode) {
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
    private function resolvePhpDocInfo(Node $node): PhpDocInfo
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $expression = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($expression instanceof Node) {
            $expressionPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($expression);
        }

        return $expressionPhpDocInfo ?? $phpDocInfo;
    }
}
