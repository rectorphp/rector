<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TypeNodeAnalyzerTest
 */
final class TypeNodeAnalyzer
{
    public function isIntersectionAndNotNullable(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode) : bool
    {
        if (!$typeNode instanceof \PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode) {
            return \false;
        }
        foreach ($typeNode->types as $subType) {
            if ($subType instanceof \PHPStan\PhpDocParser\Ast\Type\NullableTypeNode) {
                return \false;
            }
        }
        return \true;
    }
}
