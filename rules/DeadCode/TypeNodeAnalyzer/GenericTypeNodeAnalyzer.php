<?php

declare (strict_types=1);
namespace Rector\DeadCode\TypeNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
final class GenericTypeNodeAnalyzer
{
    public function hasGenericType(\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode $unionTypeNode) : bool
    {
        $types = $unionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode) {
                return \true;
            }
        }
        return \false;
    }
}
