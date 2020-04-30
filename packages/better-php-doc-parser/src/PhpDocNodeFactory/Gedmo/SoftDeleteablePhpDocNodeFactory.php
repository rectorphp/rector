<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Gedmo;

use Gedmo\Mapping\Annotation\SoftDeleteable;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SoftDeleteableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractBasicPropertyPhpDocNodeFactory;

final class SoftDeleteablePhpDocNodeFactory extends AbstractBasicPropertyPhpDocNodeFactory
{
    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [SoftDeleteable::class];
    }

    /**
     * @return SoftDeleteableTagValueNode|null
     */
    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        if (! $node instanceof Class_) {
            return null;
        }

        /** @var SoftDeleteable|null $softDeletable */
        $softDeletable = $this->nodeAnnotationReader->readClassAnnotation($node, $annotationClass);
        if ($softDeletable === null) {
            return null;
        }

        return new SoftDeleteableTagValueNode($softDeletable);
    }

    protected function getTagValueNodeClass(): string
    {
        return SoftDeleteableTagValueNode::class;
    }
}
