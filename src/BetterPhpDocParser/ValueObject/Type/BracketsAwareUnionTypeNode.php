<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Stringable;
final class BracketsAwareUnionTypeNode extends UnionTypeNode
{
    /**
     * @readonly
     */
    private bool $isWrappedInBrackets = \false;
    /**
     * @param TypeNode[] $types
     */
    public function __construct(array $types, bool $isWrappedInBrackets = \false)
    {
        $this->isWrappedInBrackets = $isWrappedInBrackets;
        parent::__construct($types);
    }
    /**
     * Preserve common format
     */
    public function __toString(): string
    {
        $types = [];
        // get the actual strings first before array_unique
        // to avoid similar object but different printing to be treated as unique
        foreach ($this->types as $type) {
            $types[] = (string) $type;
        }
        $types = array_unique($types);
        if (!$this->isWrappedInBrackets) {
            return implode('|', $types);
        }
        return '(' . implode('|', $types) . ')';
    }
    public function isWrappedInBrackets(): bool
    {
        return $this->isWrappedInBrackets;
    }
}
