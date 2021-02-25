<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TypeNodeAnalyzerTest
 */
final class TypeNodeAnalyzer
{
    public function containsArrayType(TypeNode $typeNode): bool
    {
        if ($typeNode instanceof IdentifierTypeNode) {
            return false;
        }

        if ($typeNode instanceof ArrayTypeNode) {
            return true;
        }

        if (! $typeNode instanceof UnionTypeNode) {
            return false;
        }

        foreach ($typeNode->types as $type) {
            if ($this->containsArrayType($type)) {
                return true;
            }
        }

        return false;
    }

    public function isIntersectionAndNotNullable(TypeNode $typeNode): bool
    {
        if (! $typeNode instanceof IntersectionTypeNode) {
            return false;
        }

        foreach ($typeNode->types as $type) {
            if ($type instanceof NullableTypeNode) {
                return false;
            }
        }

        return true;
    }
}
