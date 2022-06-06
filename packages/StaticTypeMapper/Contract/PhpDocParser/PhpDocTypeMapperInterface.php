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
     * @param TTypeNode $typeNode
     */
    public function mapToPHPStanType(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode, \PhpParser\Node $node, \PHPStan\Analyser\NameScope $nameScope) : \PHPStan\Type\Type;
}
