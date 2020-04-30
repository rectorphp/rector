<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Gedmo;

use Gedmo\Mapping\Annotation\Versioned;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\VersionedTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;

final class VersionedPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Versioned::class;
    }

    /**
     * @return VersionedTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Versioned|null $versioned */
        $versioned = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($versioned === null) {
            return null;
        }

        $content = $this->resolveContentFromTokenIterator($tokenIterator);
        return new VersionedTagValueNode($versioned, $content);
    }
}
