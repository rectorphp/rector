<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Contract\Ast;

use PHPStan\PhpDocParser\Ast\Node;

interface AttributeAwareNodeInterface extends Node
{
    /**
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): void;

    /**
     * @return mixed
     */
    public function getAttribute(string $name);
}
