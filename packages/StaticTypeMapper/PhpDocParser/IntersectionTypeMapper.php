<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\PhpDocParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\Analyser\NameScope;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\Type\IntersectionType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use RectorPrefix20220606\Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
use RectorPrefix20220606\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements PhpDocTypeMapperInterface<IntersectionTypeNode>
 */
final class IntersectionTypeMapper implements PhpDocTypeMapperInterface
{
    /**
     * @var \Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper
     */
    private $phpDocTypeMapper;
    public function getNodeType() : string
    {
        return IntersectionTypeNode::class;
    }
    /**
     * @required
     */
    public function autowire(PhpDocTypeMapper $phpDocTypeMapper) : void
    {
        $this->phpDocTypeMapper = $phpDocTypeMapper;
    }
    /**
     * @param IntersectionTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope) : Type
    {
        $intersectionedTypes = [];
        foreach ($typeNode->types as $intersectionedTypeNode) {
            $intersectionedTypes[] = $this->phpDocTypeMapper->mapToPHPStanType($intersectionedTypeNode, $node, $nameScope);
        }
        return new IntersectionType($intersectionedTypes);
    }
}
