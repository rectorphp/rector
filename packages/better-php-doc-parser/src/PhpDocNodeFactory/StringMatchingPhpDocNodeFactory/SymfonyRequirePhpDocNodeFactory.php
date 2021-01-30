<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\SymfonyRequiredTagValueNode;
use Rector\BetterPhpDocParser\Contract\StringTagMatchingPhpDocNodeFactoryInterface;
use Rector\PhpAttribute\ValueObject\TagName;

final class SymfonyRequirePhpDocNodeFactory implements StringTagMatchingPhpDocNodeFactoryInterface
{
    public function match(string $tag): bool
    {
        return $tag === '@' . TagName::REQUIRED;
    }

    public function createFromTokens(TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        return new SymfonyRequiredTagValueNode();
    }
}
