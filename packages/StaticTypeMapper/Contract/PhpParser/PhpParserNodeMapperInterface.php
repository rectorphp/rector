<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Contract\PhpParser;

use PhpParser\Node;
use PHPStan\Type\Type;
/**
 * @template TNode as \PhpParser\Node
 */
interface PhpParserNodeMapperInterface
{
    /**
     * @return class-string<TNode>
     */
    public function getNodeType() : string;
    /**
     * @param \PhpParser\Node $node
     */
    public function mapToPHPStan($node) : \PHPStan\Type\Type;
}
