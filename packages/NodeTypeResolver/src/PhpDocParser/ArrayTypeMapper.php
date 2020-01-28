<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\NodeTypeResolver\PhpDoc\PhpDocTypeMapper;

final class ArrayTypeMapper implements PhpDocTypeMapperInterface
{
    /**
     * @var PhpDocTypeMapper
     */
    private $phpDocTypeMapper;

    public function getNodeType(): string
    {
        return ArrayTypeNode::class;
    }

    /**
     * @required
     */
    public function autowireGenericPhpDocTypeMapper(PhpDocTypeMapper $phpDocTypeMapper): void
    {
        $this->phpDocTypeMapper = $phpDocTypeMapper;
    }

    /**
     * @param ArrayTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        $nestedType = $this->phpDocTypeMapper->mapToPHPStanType($typeNode->type, $node, $nameScope);

        // @todo improve for key!
        return new ArrayType(new MixedType(), $nestedType);
    }
}
