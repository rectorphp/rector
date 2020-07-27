<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyDocBlockManipulator
{
    public function renameParameterNameInDocBlock(Node $node, string $currentName, string $expectedName): void
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $paramTagValueNode = $phpDocInfo->getParamTagValueNodeByName($currentName);
        if ($paramTagValueNode === null) {
            return;
        }

        $paramTagValueNode->parameterName = '$' . $expectedName;
    }
}
