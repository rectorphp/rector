<?php

declare (strict_types=1);
namespace Rector\DeadCode\TypeNodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
final class GenericTypeNodeAnalyzer
{
    public function hasGenericType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode): bool
    {
        $types = $bracketsAwareUnionTypeNode->types;
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
