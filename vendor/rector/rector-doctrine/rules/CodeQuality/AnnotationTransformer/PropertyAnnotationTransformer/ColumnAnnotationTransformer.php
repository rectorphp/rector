<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer\PropertyAnnotationTransformer;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Doctrine\CodeQuality\Contract\PropertyAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\DocTagNodeFactory;
use Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Rector\Doctrine\CodeQuality\NodeFactory\ArrayItemNodeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
final class ColumnAnnotationTransformer implements PropertyAnnotationTransformerInterface
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
        $propertyMapping = $entityMapping->matchFieldPropertyMapping($property);
        if ($propertyMapping === null) {
            return;
        }
        // rename to "name"
        if (isset($propertyMapping[EntityMappingKey::COLUMN])) {
            $propertyMapping[EntityMappingKey::NAME] = $propertyMapping[EntityMappingKey::COLUMN];
            unset($propertyMapping[EntityMappingKey::COLUMN]);
        }
        $arrayItemNodes = $this->arrayItemNodeFactory->create($propertyMapping, [EntityMappingKey::TYPE, EntityMappingKey::NAME]);
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode($arrayItemNodes, $this->getClassName());
        $propertyPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    public function getClassName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\Column';
    }
}
