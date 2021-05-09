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
    public function mapToPHPStan(\PhpParser\Node $node) : \PHPStan\Type\Type;
}
