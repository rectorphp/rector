<?php

declare (strict_types=1);
namespace Rector\DeadCode\TypeNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
final class GenericTypeNodeAnalyzer
{
    public function hasGenericType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : bool
    {
        $types = $bracketsAwareUnionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof GenericTypeNode) {
                return \true;
            }
        }
        return \false;
    }
}
