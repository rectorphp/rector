<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer;

use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Doctrine\CodeQuality\Contract\ClassAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\DocTagNodeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
final class EntityClassAnnotationTransformer implements ClassAnnotationTransformerInterface
{
    /**
     * @var string
     */
    private const REPOSITORY_CLASS_KEY = 'repositoryClass';
    public function transform(EntityMapping $entityMapping, PhpDocInfo $classPhpDocInfo) : void
    {
        $classMapping = $entityMapping->getClassMapping();
        $type = $classMapping['type'] ?? null;
        if ($type !== 'entity') {
            return;
        }
        $arrayItemNodes = [];
        $repositoryClass = $classMapping[self::REPOSITORY_CLASS_KEY] ?? null;
        if ($repositoryClass) {
            $arrayItemNodes[] = new ArrayItemNode(new StringNode($repositoryClass), self::REPOSITORY_CLASS_KEY);
        }
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode($arrayItemNodes, $this->getClassName());
        $classPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    public function getClassName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\Entity';
    }
}
