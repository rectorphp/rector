<?php

declare (strict_types=1);
namespace Rector\DeadCode\TypeNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
final class MixedArrayTypeNodeAnalyzer
{
    public function hasMixedArrayType(\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode $unionTypeNode) : bool
    {
        $types = $unionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode) {
                $typeNode = $type->type;
                if (!$typeNode instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
                    continue;
                }
                if ($typeNode->name === 'mixed') {
                    return \true;
                }
            }
        }
        return \false;
    }
}
