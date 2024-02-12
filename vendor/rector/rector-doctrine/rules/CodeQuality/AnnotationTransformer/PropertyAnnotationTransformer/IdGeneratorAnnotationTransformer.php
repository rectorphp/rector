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
final class IdGeneratorAnnotationTransformer implements PropertyAnnotationTransformerInterface
{
    /**
     * @readonly
     * @var \Rector\Doctrine\CodeQuality\NodeFactory\ArrayItemNodeFactory
     */
    private $arrayItemNodeFactory;
    /**
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/3.0/reference/basic-mapping.html#identifier-generation-strategies
     * @var string[]
     */
    private const AVAILABLE_STRATEGIES = ['auto', 'sequence', 'identity', 'none', 'custom'];
    public function __construct(ArrayItemNodeFactory $arrayItemNodeFactory)
    {
        $this->arrayItemNodeFactory = $arrayItemNodeFactory;
    }
    public function transform(EntityMapping $entityMapping, PhpDocInfo $propertyPhpDocInfo, Property $property) : void
    {
        $idMapping = $entityMapping->matchIdPropertyMapping($property);
        if (!\is_array($idMapping)) {
            return;
        }
        $generator = $idMapping[EntityMappingKey::GENERATOR] ?? null;
        if (!\is_array($generator)) {
            return;
        }
        // make sure strategy is uppercase as constant value
        $generator = $this->normalizeStrategy($generator);
        $arrayItemNodes = $this->arrayItemNodeFactory->create($generator, [EntityMappingKey::STRATEGY]);
        $spacelessPhpDocTagNode = DocTagNodeFactory::createSpacelessPhpDocTagNode($arrayItemNodes, $this->getClassName());
        $propertyPhpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
    }
    public function getClassName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\GeneratedValue';
    }
    /**
     * @param array<string, mixed> $generator
     * @return array<string, mixed>
     */
    private function normalizeStrategy(array $generator) : array
    {
        if (isset($generator[EntityMappingKey::STRATEGY]) && \in_array($generator[EntityMappingKey::STRATEGY], self::AVAILABLE_STRATEGIES, \true)) {
            $generator[EntityMappingKey::STRATEGY] = \strtoupper($generator[EntityMappingKey::STRATEGY]);
        }
        return $generator;
    }
}
