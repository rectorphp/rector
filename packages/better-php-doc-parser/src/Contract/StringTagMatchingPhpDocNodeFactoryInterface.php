<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;

interface StringTagMatchingPhpDocNodeFactoryInterface
{
    public function match(string $tag): bool;

    public function createFromTokens(TokenIterator $tokenIterator): ?PhpDocTagValueNode;
}
