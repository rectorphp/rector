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
     * @var bool
     */
    private $isWrappedInBrackets = \false;
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
    public function __toString() : string
    {
        if (!$this->isWrappedInBrackets) {
            return \implode('|', $this->types);
        }
        return '(' . \implode('|', $this->types) . ')';
    }
    public function isWrappedInBrackets() : bool
    {
        return $this->isWrappedInBrackets;
    }
}
