<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Symfony;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;

final class SymfonyRouteTagValueNodeFactory
{
    public function create(): DoctrineAnnotationTagValueNode
    {
        return $this->createFromItems([]);
    }

    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items): DoctrineAnnotationTagValueNode
    {
        return new DoctrineAnnotationTagValueNode(
            'Symfony\Component\Routing\Annotation\Route',
            null,
            $items,
            'path'
        );
    }
}
