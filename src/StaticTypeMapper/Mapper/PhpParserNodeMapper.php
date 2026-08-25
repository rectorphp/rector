<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Mapper;

use PhpParser\Node;
use PHPStan\Type\Type;
use Rector\Exception\NotImplementedYetException;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
final class PhpParserNodeMapper
{
    /**
     * @var PhpParserNodeMapperInterface[]
     * @readonly
     */
    private array $phpParserNodeMappers;
    /**
     * @param PhpParserNodeMapperInterface[] $phpParserNodeMappers
     */
    public function __construct(array $phpParserNodeMappers)
    {
        $this->phpParserNodeMappers = $phpParserNodeMappers;
    }
    public function mapToPHPStanType(Node $node): Type
    {
        $matchedNodeMapper = null;
        $matchedNodeType = null;
        foreach ($this->phpParserNodeMappers as $phpParserNodeMapper) {
            $nodeType = $phpParserNodeMapper->getNodeType();
            if (!$node instanceof $nodeType) {
                continue;
            }
            // pick the most specific mapper: a mapper for a child node wins over one for its
            // parent node, regardless of registration order
            if ($matchedNodeType === null || is_a($nodeType, $matchedNodeType, \true)) {
                $matchedNodeType = $nodeType;
                $matchedNodeMapper = $phpParserNodeMapper;
            }
        }
        if (!$matchedNodeMapper instanceof PhpParserNodeMapperInterface) {
            throw new NotImplementedYetException(get_class($node));
        }
        return $matchedNodeMapper->mapToPHPStan($node);
    }
}
