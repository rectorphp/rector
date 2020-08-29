<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareUnionTypeNode extends UnionTypeNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * @var bool
     */
    private $isWrappedWithBrackets = false;

    /**
     * @param TypeNode[] $types
     */
    public function __construct(array $types, string $originalContent = '')
    {
        parent::__construct($types);

        $this->isWrappedWithBrackets = (bool) Strings::match($originalContent, '#^\((.*?)\)#');
    }

    /**
     * Preserve common format
     */
    public function __toString(): string
    {
        if (! $this->isWrappedWithBrackets) {
            return implode('|', $this->types);
        }

        return '(' . implode('|', $this->types) . ')';
    }

    public function isWrappedWithBrackets(): bool
    {
        return $this->isWrappedWithBrackets;
    }
}
