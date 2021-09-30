<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
final class NullableTypeMapper implements \Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface
{
    /**
     * @var \Rector\StaticTypeMapper\PhpDocParser\IdentifierTypeMapper
     */
    private $identifierTypeMapper;
    /**
     * @var \PHPStan\PhpDoc\TypeNodeResolver
     */
    private $typeNodeResolver;
    public function __construct(\Rector\StaticTypeMapper\PhpDocParser\IdentifierTypeMapper $identifierTypeMapper, \PHPStan\PhpDoc\TypeNodeResolver $typeNodeResolver)
    {
        $this->identifierTypeMapper = $identifierTypeMapper;
        $this->typeNodeResolver = $typeNodeResolver;
    }
    /**
     * @return class-string<TypeNode>
     */
    public function getNodeType() : string
    {
        return \PHPStan\PhpDocParser\Ast\Type\NullableTypeNode::class;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\NameScope $nameScope
     */
    public function mapToPHPStanType($typeNode, $node, $nameScope) : \PHPStan\Type\Type
    {
        $type = $typeNode->type;
        if ($type instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
            $type = $this->identifierTypeMapper->mapToPHPStanType($type, $node, $nameScope);
            return new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), $type]);
        }
        // fallback to PHPStan resolver
        return $this->typeNodeResolver->resolve($typeNode, $nameScope);
    }
}
