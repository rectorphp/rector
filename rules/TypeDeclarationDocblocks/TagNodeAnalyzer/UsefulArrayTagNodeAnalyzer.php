<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\TagNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
final class UsefulArrayTagNodeAnalyzer
{
    /**
     * @param null|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode $tagValueNode
     */
    public function isUsefulArrayTag($tagValueNode): bool
    {
        if (!$tagValueNode instanceof ReturnTagValueNode && !$tagValueNode instanceof ParamTagValueNode) {
            return \false;
        }
        $type = $tagValueNode->type;
        if (!$type instanceof IdentifierTypeNode) {
            return \true;
        }
        return $type->name !== 'array';
    }
}
