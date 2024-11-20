<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
/**
 * @implements PhpDocTypeMapperInterface<IntersectionTypeNode>
 */
final class IntersectionPhpDocTypeMapper implements PhpDocTypeMapperInterface
{
    /**
     * @readonly
     */
    private \Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper $identifierPhpDocTypeMapper;
    public function __construct(\Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper $identifierPhpDocTypeMapper)
    {
        $this->identifierPhpDocTypeMapper = $identifierPhpDocTypeMapper;
    }
    public function getNodeType() : string
    {
        return IntersectionTypeNode::class;
    }
    /**
     * @param IntersectionTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope) : Type
    {
        $intersectionedTypes = [];
        foreach ($typeNode->types as $intersectionedTypeNode) {
            if (!$intersectionedTypeNode instanceof IdentifierTypeNode) {
                return new MixedType();
            }
            $intersectionedTypes[] = $this->identifierPhpDocTypeMapper->mapIdentifierTypeNode($intersectionedTypeNode, $node);
        }
        return new IntersectionType($intersectionedTypes);
    }
}
