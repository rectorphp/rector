<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\PhpDocNode;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\Symfony\Enum\SymfonyAnnotation;
final class SymfonyRouteTagValueNodeFactory
{
    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items) : DoctrineAnnotationTagValueNode
    {
        return new DoctrineAnnotationTagValueNode(new IdentifierTypeNode(SymfonyAnnotation::ROUTE), null, $items, 'path');
    }
}
