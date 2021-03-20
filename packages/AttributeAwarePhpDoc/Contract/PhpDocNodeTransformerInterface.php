<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Contract;

use PHPStan\PhpDocParser\Ast\Node;

interface PhpDocNodeTransformerInterface
{
    public function isMatch(Node $node): bool;

    /**
     * @template T of Node
     * @param T $node
     * @return T
     */
    public function transform(Node $node, string $docContent): Node;
}
