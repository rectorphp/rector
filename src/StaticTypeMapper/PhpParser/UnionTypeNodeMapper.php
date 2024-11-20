<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UnionType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
/**
 * @implements PhpParserNodeMapperInterface<UnionType>
 */
final class UnionTypeNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private \Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper $fullyQualifiedNodeMapper;
    /**
     * @readonly
     */
    private \Rector\StaticTypeMapper\PhpParser\NameNodeMapper $nameNodeMapper;
    /**
     * @readonly
     */
    private \Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper $identifierNodeMapper;
    /**
     * @readonly
     */
    private \Rector\StaticTypeMapper\PhpParser\IntersectionTypeNodeMapper $intersectionTypeNodeMapper;
    public function __construct(TypeFactory $typeFactory, \Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper $fullyQualifiedNodeMapper, \Rector\StaticTypeMapper\PhpParser\NameNodeMapper $nameNodeMapper, \Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper $identifierNodeMapper, \Rector\StaticTypeMapper\PhpParser\IntersectionTypeNodeMapper $intersectionTypeNodeMapper)
    {
        $this->typeFactory = $typeFactory;
        $this->fullyQualifiedNodeMapper = $fullyQualifiedNodeMapper;
        $this->nameNodeMapper = $nameNodeMapper;
        $this->identifierNodeMapper = $identifierNodeMapper;
        $this->intersectionTypeNodeMapper = $intersectionTypeNodeMapper;
    }
    public function getNodeType() : string
    {
        return UnionType::class;
    }
    /**
     * @param UnionType $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        $types = [];
        foreach ($node->types as $unionedType) {
            if ($unionedType instanceof FullyQualified) {
                $types[] = $this->fullyQualifiedNodeMapper->mapToPHPStan($unionedType);
                continue;
            }
            if ($unionedType instanceof Name) {
                $types[] = $this->nameNodeMapper->mapToPHPStan($unionedType);
                continue;
            }
            if ($unionedType instanceof Identifier) {
                $types[] = $this->identifierNodeMapper->mapToPHPStan($unionedType);
                continue;
            }
            $types[] = $this->intersectionTypeNodeMapper->mapToPHPStan($unionedType);
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types, \true);
    }
}
