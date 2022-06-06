<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\Contract\PhpParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\Type\Type;
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
     * @param TNode $node
     */
    public function mapToPHPStan(Node $node) : Type;
}
