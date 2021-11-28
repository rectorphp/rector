<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Contract\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
/**
 * @template TTypeNode as TypeNode
 */
interface PhpDocTypeMapperInterface
{
    /**
     * @return class-string<TTypeNode>
     */
    public function getNodeType() : string;
    /**
     * @param \PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\NameScope $nameScope
     */
    public function mapToPHPStanType($typeNode, $node, $nameScope) : \PHPStan\Type\Type;
}
