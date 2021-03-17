<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\StringTagMatchingPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\ApiPhpDocTagNode;

final class ApiPhpDocNodeFactory implements StringTagMatchingPhpDocNodeFactoryInterface
{
    public function createFromTokens(TokenIterator $tokenIterator): ?Node
    {
        return new ApiPhpDocTagNode();
    }

    public function match(string $tag): bool
    {
        return strtolower($tag) === ApiPhpDocTagNode::NAME;
    }
}
