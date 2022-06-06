<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\PhpParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use RectorPrefix20220606\Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
/**
 * @implements PhpParserNodeMapperInterface<Identifier>
 */
final class IdentifierNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper
     */
    private $scalarStringToTypeMapper;
    public function __construct(ScalarStringToTypeMapper $scalarStringToTypeMapper)
    {
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
    }
    public function getNodeType() : string
    {
        return Identifier::class;
    }
    /**
     * @param Identifier $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        return $this->scalarStringToTypeMapper->mapScalarStringToType($node->name);
    }
}
