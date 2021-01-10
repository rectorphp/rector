<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Rector\PhpdocParserPrinter\Attributes\AttributesTrait;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

final class AttributeAwareIntersectionTypeNode extends IntersectionTypeNode implements AttributeAwareInterface
{
    use AttributesTrait;

    public function __toString(): string
    {
        return implode('&', $this->types);
    }
}
