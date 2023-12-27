<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer\ClassAnnotationTransformer;

use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Doctrine\CodeQuality\Contract\ClassAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\DocTagNodeFactory;
use Rector\Doctrine\CodeQuality\NodeFactory\ArrayItemNodeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
final class InheritanceClassAnnotationTransformer implements ClassAnnotationTransformerInterface
{
    /**
     * @readonly
     * @var \Rector\Doctrine\CodeQuality\NodeFactory\ArrayItemNodeFactory
     */
    private $arrayItemNodeFactory;
    public function __construct(ArrayItemNodeFactory $arrayItemNodeFactory)
    {
        $this->arrayItemNodeFactory = $arrayItemNodeFactory;
    }
    public function transform(EntityMapping $entityMapping, PhpDocInfo $classPhpDocInfo) : void
    {
        $classMapping = $entityMapping->getClassMapping();
        $inheritanceType = $classMapping['inheritanceType'] ?? null;
        if ($inheritanceType === null) {
            return;
        }
        $inheritanceStringNode = new StringNode($inheritanceType);
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode([$inheritanceStringNode], 'Doctrine\\ORM\\Mapping\\InheritanceType');
        $classPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
        if (isset($classMapping['discriminatorColumn'])) {
            $this->addDisriminatorColumn($classMapping['discriminatorColumn'], $classPhpDocInfo);
        }
        if (isset($classMapping['discriminatorMap'])) {
            $this->addDiscriminatorMap($classMapping['discriminatorMap'], $classPhpDocInfo);
        }
    }
    /**
     * @param array<string, mixed> $discriminatorColumn
     */
    private function addDisriminatorColumn(array $discriminatorColumn, PhpDocInfo $classPhpDocInfo) : void
    {
        $arrayItemNodes = $this->arrayItemNodeFactory->create($discriminatorColumn, ['name', 'type']);
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode($arrayItemNodes, 'Doctrine\\ORM\\Mapping\\DiscriminatorColumn');
        $classPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    /**
     * @param array<string, mixed> $discriminatorMap
     */
    private function addDiscriminatorMap(array $discriminatorMap, PhpDocInfo $classPhpDocInfo) : void
    {
        $arrayItemNodes = $this->arrayItemNodeFactory->create($discriminatorMap, [ArrayItemNodeFactory::QUOTE_ALL]);
        $curlyListNode = new CurlyListNode($arrayItemNodes);
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode([$curlyListNode], 'Doctrine\\ORM\\Mapping\\DiscriminatorMap');
        $classPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
}
