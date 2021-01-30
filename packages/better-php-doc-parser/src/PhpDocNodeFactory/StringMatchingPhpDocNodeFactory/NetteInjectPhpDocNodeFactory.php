<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\StringTagMatchingPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagNode;

final class NetteInjectPhpDocNodeFactory implements StringTagMatchingPhpDocNodeFactoryInterface
{
    public function match(string $tag): bool
    {
        return $tag === NetteInjectTagNode::NAME;
    }

    public function createFromTokens(TokenIterator $tokenIterator): ?PhpDocTagNode
    {
        return new NetteInjectTagNode();
    }
}
