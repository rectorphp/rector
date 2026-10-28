<?php

declare (strict_types=1);
namespace Rector\DeadCode\TypeNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
final class GenericTypeNodeAnalyzer
{
    public function hasGenericType(UnionTypeNode $unionTypeNode): bool
    {
        $types = $unionTypeNode->types;
        $found = \false;
        foreach ($types as $typeNode) {
            if ($typeNode instanceof GenericTypeNode) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
