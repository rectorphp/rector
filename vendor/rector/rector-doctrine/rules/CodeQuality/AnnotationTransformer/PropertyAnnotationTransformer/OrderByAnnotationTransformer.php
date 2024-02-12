<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Doctrine\CodeQuality\Contract\PropertyAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\DocTagNodeFactory;
use Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Rector\Doctrine\CodeQuality\NodeFactory\ArrayItemNodeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
final class OrderByAnnotationTransformer implements PropertyAnnotationTransformerInterface
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
        $oneToManyMapping = $entityMapping->matchOneToManyPropertyMapping($property);
        if (!\is_array($oneToManyMapping)) {
            return;
        }
        // we handle OrderBy here only
        if (!isset($oneToManyMapping[EntityMappingKey::ORDER_BY])) {
            return;
        }
        $orderBy = $oneToManyMapping[EntityMappingKey::ORDER_BY];
        $arrayItemNodes = $this->arrayItemNodeFactory->createWithQuotes($orderBy);
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode([new CurlyListNode($arrayItemNodes)], $this->getClassName());
        $propertyPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    public function getClassName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\OrderBy';
    }
}
