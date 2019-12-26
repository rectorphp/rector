<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;

final class PhpParserTypeAnalyzer
{
    /**
     * @param Name|NullableType|UnionType|Identifier $possibleSubtype
     * @param Name|NullableType|UnionType|Identifier $possibleParentType
     */
    public function isSubtypeOf(Node $possibleSubtype, Node $possibleParentType): bool
    {
        // skip until PHP 8 is out
        if ($possibleSubtype instanceof UnionType || $possibleParentType instanceof UnionType) {
            return false;
        }

        // possible - https://3v4l.org/ZuJCh
        if ($possibleSubtype instanceof NullableType && ! $possibleParentType instanceof NullableType) {
            return $this->isSubtypeOf($possibleSubtype->type, $possibleParentType);
        }

        // not possible - https://3v4l.org/iNDTc
        if (! $possibleSubtype instanceof NullableType && $possibleParentType instanceof NullableType) {
            return false;
        }

        // unwrap nullable types
        if ($possibleParentType instanceof NullableType) {
            $possibleParentType = $possibleParentType->type;
        }

        if ($possibleSubtype instanceof NullableType) {
            $possibleSubtype = $possibleSubtype->type;
        }

        $possibleSubtype = $possibleSubtype->toString();
        $possibleParentType = $possibleParentType->toString();
        if (is_a($possibleSubtype, $possibleParentType, true)) {
            return true;
        }

        if (in_array($possibleSubtype, ['array', 'Traversable'], true) && $possibleParentType === 'iterable') {
            return true;
        }

        if (in_array($possibleSubtype, ['array', 'ArrayIterator'], true) && $possibleParentType === 'countable') {
            return true;
        }

        if ($possibleParentType === $possibleSubtype) {
            return true;
        }

        return ctype_upper($possibleSubtype[0]) && $possibleParentType === 'object';
    }
}
