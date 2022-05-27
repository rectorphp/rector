<?php

declare (strict_types=1);
namespace Rector\DeadCode\TypeNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
final class GenericTypeNodeAnalyzer
{
    public function hasGenericType(UnionTypeNode $unionTypeNode) : bool
    {
        $types = $unionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof GenericTypeNode) {
                return \true;
            }
        }
        return \false;
    }
}
