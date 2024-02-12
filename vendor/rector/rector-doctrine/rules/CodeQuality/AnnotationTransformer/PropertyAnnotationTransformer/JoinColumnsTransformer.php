<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer;

use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
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
            $joinColumnSpacelessPhpDocTagNode = $this->createJoinColumnSpacelessTagValueNode($columnName, $joinColumn);
            $joinColumnArrayItemNodes[] = new ArrayItemNode($joinColumnSpacelessPhpDocTagNode);
        }
        if (\count($joinColumnArrayItemNodes) === 1) {
            $spacelessPhpDocTagNode = $joinColumnArrayItemNodes[0]->value;
        } else {
            $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode([new CurlyListNode($joinColumnArrayItemNodes)], $this->getClassName());
        }
        $propertyPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    public function getClassName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\JoinColumns';
    }
    /**
     * @param int|string $columnName
     * @param mixed $joinColumn
     */
    private function createJoinColumnSpacelessTagValueNode($columnName, $joinColumn) : SpacelessPhpDocTagNode
    {
        $joinColumn = \array_merge(['name' => $columnName], $joinColumn);
        $arrayItemNodes = $this->arrayItemNodeFactory->create($joinColumn, ['name', 'referencedColumnName']);
        return DocTagNodeFactory::createSpacelessPhpDocTagNode($arrayItemNodes, 'Doctrine\\ORM\\Mapping\\JoinColumn');
    }
}
