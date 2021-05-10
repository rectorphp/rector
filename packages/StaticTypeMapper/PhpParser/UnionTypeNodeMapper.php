<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\UnionType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;

final class UnionTypeNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @var PhpParserNodeMapper
     */
    private $phpParserNodeMapper;

    public function __construct(
        private TypeFactory $typeFactory
    ) {
    }

    /**
     * @required
     */
    public function autowireUnionTypeNodeMapper(PhpParserNodeMapper $phpParserNodeMapper): void
    {
        $this->phpParserNodeMapper = $phpParserNodeMapper;
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return UnionType::class;
    }

    /**
     * @param UnionType $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        $types = [];
        foreach ($node->types as $unionedType) {
            $types[] = $this->phpParserNodeMapper->mapToPHPStanType($unionedType);
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
