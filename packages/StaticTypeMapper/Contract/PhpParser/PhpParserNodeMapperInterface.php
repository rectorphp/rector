<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Contract\PhpParser;

use PhpParser\Node;
use PHPStan\Type\Type;
interface PhpParserNodeMapperInterface
{
    /**
     * @return class-string<Node>
     */
    public function getNodeType() : string;
    /**
     * @param \PhpParser\Node $node
     */
    public function mapToPHPStan($node) : \PHPStan\Type\Type;
}
