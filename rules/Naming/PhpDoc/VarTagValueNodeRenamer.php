<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\PhpDoc;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
final class VarTagValueNodeRenamer
{
    public function renameAssignVarTagVariableName(PhpDocInfo $phpDocInfo, string $originalName, string $expectedName) : void
    {
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return;
        }
        if ($varTagValueNode->variableName !== '$' . $originalName) {
            return;
        }
        $varTagValueNode->variableName = '$' . $expectedName;
        // invoke node reprint - same as in php-parser
        $varTagValueNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
    }
}
