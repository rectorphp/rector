<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\NodeTypeResolver\TypeMapper\ScalarStringToTypeMapper;

final class IdentifierNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @var ScalarStringToTypeMapper
     */
    private $scalarStringToTypeMapper;

    public function __construct(ScalarStringToTypeMapper $scalarStringToTypeMapper)
    {
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
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
        if ($node->name === 'string') {
            return new StringType();
        }

        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($node->name);
        if ($type !== null) {
            return $type;
        }

        return new MixedType();
    }
}
