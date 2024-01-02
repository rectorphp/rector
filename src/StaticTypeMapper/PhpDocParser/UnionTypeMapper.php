<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
/**
 * @implements PhpDocTypeMapperInterface<UnionTypeNode>
 */
final class UnionTypeMapper implements PhpDocTypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpDocParser\IdentifierTypeMapper
     */
    private $identifierTypeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpDocParser\IntersectionTypeMapper
     */
    private $intersectionTypeMapper;
    /**
     * @readonly
     * @var \PHPStan\PhpDoc\TypeNodeResolver
     */
    private $typeNodeResolver;
    public function __construct(TypeFactory $typeFactory, \Rector\StaticTypeMapper\PhpDocParser\IdentifierTypeMapper $identifierTypeMapper, \Rector\StaticTypeMapper\PhpDocParser\IntersectionTypeMapper $intersectionTypeMapper, TypeNodeResolver $typeNodeResolver)
    {
        $this->typeFactory = $typeFactory;
        $this->identifierTypeMapper = $identifierTypeMapper;
        $this->intersectionTypeMapper = $intersectionTypeMapper;
        $this->typeNodeResolver = $typeNodeResolver;
    }
    public function getNodeType() : string
    {
        return UnionTypeNode::class;
    }
    /**
     * @param UnionTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope) : Type
    {
        $unionedTypes = [];
        foreach ($typeNode->types as $unionedTypeNode) {
            if ($unionedTypeNode instanceof IdentifierTypeNode) {
                $unionedTypes[] = $this->identifierTypeMapper->mapToPHPStanType($unionedTypeNode, $node, $nameScope);
                continue;
            }
            if ($unionedTypeNode instanceof IntersectionTypeNode) {
                $unionedTypes[] = $this->intersectionTypeMapper->mapToPHPStanType($unionedTypeNode, $node, $nameScope);
                continue;
            }
            $unionedTypes[] = $this->typeNodeResolver->resolve($unionedTypeNode, $nameScope);
        }
        // to prevent missing class error, e.g. in tests
        return $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($unionedTypes);
    }
}
