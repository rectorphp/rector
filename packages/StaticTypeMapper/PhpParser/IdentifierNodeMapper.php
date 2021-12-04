<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;

/**
 * @implements PhpParserNodeMapperInterface<Identifier>
 */
final class IdentifierNodeMapper implements PhpParserNodeMapperInterface
{
    public function __construct(
        private readonly ScalarStringToTypeMapper $scalarStringToTypeMapper
    ) {
    }

    public function getNodeType(): string
    {
        return Identifier::class;
    }

    /**
     * @param Identifier $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        return $this->scalarStringToTypeMapper->mapScalarStringToType($node->name);
    }
}
