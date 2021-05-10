<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Symfony;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
final class SymfonyRouteTagValueNodeFactory
{
    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items) : \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        return new \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode('Symfony\\Component\\Routing\\Annotation\\Route', null, $items, 'path');
    }
}
