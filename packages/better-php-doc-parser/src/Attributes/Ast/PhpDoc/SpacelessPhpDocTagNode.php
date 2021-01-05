<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\PhpdocParserPrinter\Attributes\AttributesTrait;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

/**
 * Useful for annotation class based annotation, e.g. @ORM\Entity to prevent space
 * between the @ORM\Entity and (someContent)
 */
final class SpacelessPhpDocTagNode extends PhpDocTagNode implements AttributeAwareInterface
{
    use AttributesTrait;

    public function __toString(): string
    {
        return $this->name . $this->value;
    }
}
