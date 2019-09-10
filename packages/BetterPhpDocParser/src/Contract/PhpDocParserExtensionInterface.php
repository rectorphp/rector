<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;

interface PhpDocParserExtensionInterface
{
    public function matchTag(string $tag): bool;

    public function parse(TokenIterator $tokenIterator, string $tag): ?PhpDocTagValueNode;
}
