<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Gedmo;

use Gedmo\Mapping\Annotation\Locale;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\LocaleTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractBasicPropertyPhpDocNodeFactory;

final class LocalePhpDocNodeFactory extends AbstractBasicPropertyPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Locale::class;
    }

    /**
     * @return LocaleTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        return $this->createFromPropertyNode($node);
    }

    protected function getTagValueNodeClass(): string
    {
        return LocaleTagValueNode::class;
    }
}
