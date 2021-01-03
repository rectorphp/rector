<?php
declare(strict_types=1);

namespace Rector\CodingStyle\DocBlock;

use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\PhpdocParserPrinter\ValueObject\PhpDocNode\AttributeAwarePhpDocTagNode;

final class ThrowsFactory
{
    public function crateDocTagNodeFromClass(string $throwableClass): AttributeAwarePhpDocTagNode
    {
        $throwsTagValueNode = new ThrowsTagValueNode(new IdentifierTypeNode($throwableClass), '');

        return new AttributeAwarePhpDocTagNode('@throws', $throwsTagValueNode);
    }
}
