<?php

declare(strict_types=1);

namespace Rector\Naming\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class VarTagValueNodeRenamer
{
    public function renameAssignVarTagVariableName(
        PhpDocInfo $phpDocInfo,
        string $originalName,
        string $expectedName
    ): void {
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $varTagValueNode instanceof VarTagValueNode) {
            return;
        }

        if ($varTagValueNode->variableName !== '$' . $originalName) {
            return;
        }

        $varTagValueNode->variableName = '$' . $expectedName;
        $phpDocInfo->markAsChanged();
    }
}
