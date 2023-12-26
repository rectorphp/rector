<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
final class DocTagNodeFactory
{
    /**
     * @param ArrayItemNode[] $arrayItemNodes
     */
    public static function createSpacelessPhpDocTagNode(array $arrayItemNodes, string $className) : SpacelessPhpDocTagNode
    {
        $columDoctrineAnnotationTagValueNode = new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('@\\' . $className), null, $arrayItemNodes);
        return new SpacelessPhpDocTagNode('@\\' . $className, $columDoctrineAnnotationTagValueNode);
    }
}
