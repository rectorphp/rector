<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Doctrine\CodeQuality\Contract\ClassAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\DocTagNodeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
final class EmbeddableClassAnnotationTransformer implements ClassAnnotationTransformerInterface
{
    public function transform(EntityMapping $entityMapping, PhpDocInfo $classPhpDocInfo) : void
    {
        $classMapping = $entityMapping->getClassMapping();
        $type = $classMapping['type'] ?? null;
        if ($type !== 'embeddable') {
            return;
        }
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode([], $this->getClassName());
        $classPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    public function getClassName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\Embeddable';
    }
}
