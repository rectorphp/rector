<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Doctrine\CodeQuality\Contract\PropertyAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\DocTagNodeFactory;
use Rector\Doctrine\CodeQuality\NodeFactory\ArrayItemNodeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
final class JoinColumnsTransformer implements PropertyAnnotationTransformerInterface
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
    public function transform(EntityMapping $entityMapping, PhpDocInfo $propertyPhpDocInfo, Property $property) : void
    {
        $manyToOnePropertyMapping = $entityMapping->matchManyToOnePropertyMapping($property);
        if (!\is_array($manyToOnePropertyMapping)) {
            return;
        }
        $joinColumns = $manyToOnePropertyMapping['joinColumns'] ?? null;
        if (!\is_array($joinColumns)) {
            return;
        }
        $joinColumnArrayItemNodes = [];
        foreach ($joinColumns as $columnName => $joinColumn) {
            $joinColumn = \array_merge(['name' => $columnName], $joinColumn);
            $arrayItemNodes = $this->arrayItemNodeFactory->create($joinColumn, ['name', 'referencedColumnName']);
            $joinColumnSpacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode($arrayItemNodes, 'Doctrine\\ORM\\Mapping\\JoinColumn');
            $joinColumnArrayItemNodes[] = new ArrayItemNode($joinColumnSpacelessPhpDocTagNode);
        }
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode($joinColumnArrayItemNodes, $this->getClassName());
        $propertyPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    public function getClassName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\JoinColumns';
    }
}
