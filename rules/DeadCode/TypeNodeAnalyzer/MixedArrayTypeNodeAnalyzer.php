<?php

declare (strict_types=1);
namespace Rector\DeadCode\TypeNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
final class MixedArrayTypeNodeAnalyzer
{
    public function hasMixedArrayType(UnionTypeNode $unionTypeNode): bool
    {
        $types = $unionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof ArrayTypeNode) {
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
