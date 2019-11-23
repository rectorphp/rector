<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\PhpDocNode;

use PHPStan\PhpDocParser\Ast\Node;

interface AttributeAwareNodeInterface extends Node
{
    public function setAttribute(string $name, $value): void;

    public function getAttribute(string $name);
}
