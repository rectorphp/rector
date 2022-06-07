<?php

declare (strict_types=1);
namespace Rector\Symfony\PhpDocNode;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Symfony\Enum\SymfonyAnnotation;
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
