<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\StringTagMatchingPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagValueNode;
use Rector\PhpAttribute\ValueObject\TagName;

final class NetteInjectPhpDocNodeFactory implements StringTagMatchingPhpDocNodeFactoryInterface
{
    public function match(string $tag): bool
    {
        return $tag === '@' . TagName::INJECT;
    }

    public function createFromTokens(TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        return new NetteInjectTagValueNode([]);
    }
}
