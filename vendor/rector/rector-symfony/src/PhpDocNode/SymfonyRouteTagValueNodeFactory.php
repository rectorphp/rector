<?php

declare (strict_types=1);
namespace Rector\Symfony\PhpDocNode;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Symfony\Enum\SymfonyAnnotation;
final class SymfonyRouteTagValueNodeFactory
{
    /**
     * @param ArrayItemNode[] $arrayItemNodes
     */
    public function createFromItems(array $arrayItemNodes) : DoctrineAnnotationTagValueNode
    {
        $identifierTypeNode = new IdentifierTypeNode(SymfonyAnnotation::ROUTE);
        return new DoctrineAnnotationTagValueNode($identifierTypeNode, null, $arrayItemNodes, 'path');
    }
}
