<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Parser\TokenIterator;

interface StringTagMatchingPhpDocNodeFactoryInterface
{
    public function match(string $tag): bool;

    public function createFromTokens(TokenIterator $tokenIterator): ?Node;
}
