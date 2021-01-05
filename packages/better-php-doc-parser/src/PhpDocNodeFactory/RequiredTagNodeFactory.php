<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\Symfony\ValueObject\PhpDocNode\RequiredTagValueNode;

final class RequiredTagNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        return new RequiredTagValueNode();
    }
}
