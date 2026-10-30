<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\TagNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
final class UsefulArrayTagNodeAnalyzer
{
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode|null $tagValueNode
     */
    public function isUsefulArrayTag($tagValueNode): bool
    {
        if (!$tagValueNode instanceof ReturnTagValueNode && !$tagValueNode instanceof ParamTagValueNode && !$tagValueNode instanceof VarTagValueNode) {
            return \false;
        }
        $type = $tagValueNode->type;
        if (!$type instanceof IdentifierTypeNode) {
            return !$this->isMixedArray($type);
        }
        return !in_array($type->name, ['array', 'mixed', 'iterable'], \true);
    }
    private function isMixedArray(TypeNode $typeNode): bool
    {
        return $typeNode instanceof ArrayTypeNode && $typeNode->type instanceof IdentifierTypeNode && $typeNode->type->name === 'mixed';
    }
}
