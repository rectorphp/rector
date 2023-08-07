<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
/**
 * @implements PhpParserNodeMapperInterface<NullableType>
 */
final class NullableTypeNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper
     */
    private $fullyQualifiedNodeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpParser\NameNodeMapper
     */
    private $nameNodeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper
     */
    private $identifierNodeMapper;
    public function __construct(TypeFactory $typeFactory, \Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper $fullyQualifiedNodeMapper, \Rector\StaticTypeMapper\PhpParser\NameNodeMapper $nameNodeMapper, \Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper $identifierNodeMapper)
    {
        $this->typeFactory = $typeFactory;
        $this->fullyQualifiedNodeMapper = $fullyQualifiedNodeMapper;
        $this->nameNodeMapper = $nameNodeMapper;
        $this->identifierNodeMapper = $identifierNodeMapper;
    }
    public function getNodeType() : string
    {
        return NullableType::class;
    }
    /**
     * @param NullableType $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        if ($node->type instanceof FullyQualified) {
            $type = $this->fullyQualifiedNodeMapper->mapToPHPStan($node->type);
        } elseif ($node->type instanceof Name) {
            $type = $this->nameNodeMapper->mapToPHPStan($node->type);
        } else {
            $type = $this->identifierNodeMapper->mapToPHPStan($node->type);
        }
        $types = [$type, new NullType()];
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
