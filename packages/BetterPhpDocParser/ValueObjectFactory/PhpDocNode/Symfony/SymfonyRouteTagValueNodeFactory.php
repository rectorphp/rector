<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Symfony;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;

final class SymfonyRouteTagValueNodeFactory
{
    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items): DoctrineAnnotationTagValueNode
    {
        return new DoctrineAnnotationTagValueNode(
            new IdentifierTypeNode('Symfony\Component\Routing\Annotation\Route'),
            null,
            $items,
            'path'
        );
    }
}
