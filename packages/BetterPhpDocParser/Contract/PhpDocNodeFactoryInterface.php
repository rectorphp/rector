<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;

/**
 * @deprecated Use DoctrineAnnotatoinTagValueNode instead
 */
interface PhpDocNodeFactoryInterface
{
    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode;
}
