<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
use RectorPrefix20211123\Symfony\Contracts\Service\Attribute\Required;
final class IntersectionTypeMapper implements \Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface
{
    /**
     * @var \Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper
     */
    private $phpDocTypeMapper;
    /**
     * @return class-string<TypeNode>
     */
    public function getNodeType() : string
    {
        return \PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode::class;
    }
    /**
     * @required
     */
    public function autowireUnionTypeMapper(\Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper $phpDocTypeMapper) : void
    {
        $this->phpDocTypeMapper = $phpDocTypeMapper;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\NameScope $nameScope
     */
    public function mapToPHPStanType($typeNode, $node, $nameScope) : \PHPStan\Type\Type
    {
        $intersectionedTypes = [];
        foreach ($typeNode->types as $intersectionedTypeNode) {
            $intersectionedTypes[] = $this->phpDocTypeMapper->mapToPHPStanType($intersectionedTypeNode, $node, $nameScope);
        }
        return new \PHPStan\Type\IntersectionType($intersectionedTypes);
    }
}
