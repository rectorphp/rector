<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation;

use PHPStan\PhpDocParser\Ast\Node;

final class CurlyListNode extends AbstractValuesAwareNode implements Node
{
    public function __toString(): string
    {
        return parent::printWithWrapper('{', '}');
    }
}
