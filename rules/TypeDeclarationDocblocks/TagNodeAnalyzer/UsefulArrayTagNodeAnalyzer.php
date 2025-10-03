<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\TagNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
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
            return !$this->isMixedArray($type);
        }
        return !in_array($type->name, ['array', 'mixed', 'iterable'], \true);
    }
    public function isMixedArray(TypeNode $typeNode): bool
    {
        return $typeNode instanceof SpacingAwareArrayTypeNode && $typeNode->type instanceof IdentifierTypeNode && $typeNode->type->name === 'mixed';
    }
}
