<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use RectorPrefix20211123\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements PhpParserNodeMapperInterface<Node\IntersectionType>
 */
final class IntersectionTypeNodeMapper implements \Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface
{
    /**
     * @var \Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper
     */
    private $phpParserNodeMapper;
    /**
     * @required
     */
    public function autowireUnionTypeNodeMapper(\Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper $phpParserNodeMapper) : void
    {
        $this->phpParserNodeMapper = $phpParserNodeMapper;
    }
    /**
     * @return class-string<Node>
     */
    public function getNodeType() : string
    {
        return \PhpParser\Node\IntersectionType::class;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function mapToPHPStan($node) : \PHPStan\Type\Type
    {
        $types = [];
        foreach ($node->types as $intersectionedType) {
            $types[] = $this->phpParserNodeMapper->mapToPHPStanType($intersectionedType);
        }
        return new \PHPStan\Type\IntersectionType($types);
    }
}
