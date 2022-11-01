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
use RectorPrefix202211\Symfony\Contracts\Service\Attribute\Required;
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
