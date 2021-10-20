<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\UnionType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required;
final class UnionTypeNodeMapper implements \Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface
{
    /**
     * @var \Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper
     */
    private $phpParserNodeMapper;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }
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
        return \PhpParser\Node\UnionType::class;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function mapToPHPStan($node) : \PHPStan\Type\Type
    {
        $types = [];
        foreach ($node->types as $unionedType) {
            $types[] = $this->phpParserNodeMapper->mapToPHPStanType($unionedType);
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
