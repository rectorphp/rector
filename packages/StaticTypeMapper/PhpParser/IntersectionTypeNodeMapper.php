<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements PhpParserNodeMapperInterface<Node\IntersectionType>
 */
final class IntersectionTypeNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @var \Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper
     */
    private $phpParserNodeMapper;
    /**
     * @required
     */
    public function autowire(PhpParserNodeMapper $phpParserNodeMapper) : void
    {
        $this->phpParserNodeMapper = $phpParserNodeMapper;
    }
    public function getNodeType() : string
    {
        return Node\IntersectionType::class;
    }
    /**
     * @param Node\IntersectionType $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        $types = [];
        foreach ($node->types as $intersectionedType) {
            $types[] = $this->phpParserNodeMapper->mapToPHPStanType($intersectionedType);
        }
        return new IntersectionType($types);
    }
}
