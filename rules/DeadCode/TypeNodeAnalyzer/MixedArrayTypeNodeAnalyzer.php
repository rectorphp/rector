<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\TypeNodeAnalyzer;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
final class MixedArrayTypeNodeAnalyzer
{
    public function hasMixedArrayType(UnionTypeNode $unionTypeNode) : bool
    {
        $types = $unionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof SpacingAwareArrayTypeNode) {
                $typeNode = $type->type;
                if (!$typeNode instanceof IdentifierTypeNode) {
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
