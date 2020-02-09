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
        $possibleParentType = $this->unwrapNullableAndToString($possibleParentType);
        $possibleSubtype = $this->unwrapNullableAndToString($possibleSubtype);

        if (is_a($possibleSubtype, $possibleParentType, true)) {
            return true;
        }

        if ($this->isTraversableOrIterableSubtype($possibleSubtype, $possibleParentType)) {
            return true;
        }

        if ($possibleParentType === $possibleSubtype) {
            return true;
        }

        return ctype_upper($possibleSubtype[0]) && $possibleParentType === 'object';
    }

    /**
     * @param Name|NullableType|Identifier $node
     */
    private function unwrapNullableAndToString(Node $node): string
    {
        if (! $node instanceof NullableType) {
            return $node->toString();
        }

        return $node->type->toString();
    }

    private function isTraversableOrIterableSubtype(string $possibleSubtype, string $possibleParentType): bool
    {
        if (in_array($possibleSubtype, ['array', 'Traversable'], true) && $possibleParentType === 'iterable') {
            return true;
        }

        return in_array($possibleSubtype, ['array', 'ArrayIterator'], true) && $possibleParentType === 'countable';
    }
}
