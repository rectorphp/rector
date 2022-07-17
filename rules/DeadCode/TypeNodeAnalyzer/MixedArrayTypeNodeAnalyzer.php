<?php

declare (strict_types=1);
namespace Rector\DeadCode\TypeNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
final class MixedArrayTypeNodeAnalyzer
{
    public function hasMixedArrayType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : bool
    {
        $types = $bracketsAwareUnionTypeNode->types;
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
