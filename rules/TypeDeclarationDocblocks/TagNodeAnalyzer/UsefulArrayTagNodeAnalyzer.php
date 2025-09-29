<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\TagNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
final class UsefulArrayTagNodeAnalyzer
{
    public function isUsefulArrayTag(?ReturnTagValueNode $returnTagValueNode): bool
    {
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return \false;
        }
        $type = $returnTagValueNode->type;
        if (!$type instanceof IdentifierTypeNode) {
            return \true;
        }
        return $type->name !== 'array';
    }
}
