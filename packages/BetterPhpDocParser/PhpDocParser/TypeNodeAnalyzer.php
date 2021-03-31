<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TypeNodeAnalyzerTest
 */
final class TypeNodeAnalyzer
{
    public function isIntersectionAndNotNullable(TypeNode $typeNode): bool
    {
        if (! $typeNode instanceof IntersectionTypeNode) {
            return false;
        }

        foreach ($typeNode->types as $subType) {
            if ($subType instanceof NullableTypeNode) {
                return false;
            }
        }

        return true;
    }
}
