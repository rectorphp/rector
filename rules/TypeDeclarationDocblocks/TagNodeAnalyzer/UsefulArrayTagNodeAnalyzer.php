<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\TagNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
final class UsefulArrayTagNodeAnalyzer
{
    /**
     * @param null|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode $tagValueNode
     */
    public function isUsefulArrayTag($tagValueNode): bool
    {
        if (!$tagValueNode instanceof ReturnTagValueNode && !$tagValueNode instanceof ParamTagValueNode && !$tagValueNode instanceof VarTagValueNode) {
            return \false;
        }
        $type = $tagValueNode->type;
        if (!$type instanceof IdentifierTypeNode) {
            return \true;
        }
        return $type->name !== 'array';
    }
}
