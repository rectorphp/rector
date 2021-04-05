<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes;

use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;

final class AttributeMirrorer
{
    public function mirror(Node $oldNode, Node $newNode): void
    {
        $parent = $oldNode->getAttribute(PhpDocAttributeKey::PARENT);
        $newNode->setAttribute(PhpDocAttributeKey::PARENT, $parent);

        $startAndEnd = $oldNode->getAttribute(PhpDocAttributeKey::START_AND_END);
        $newNode->setAttribute(PhpDocAttributeKey::PARENT, $startAndEnd);
    }
}
