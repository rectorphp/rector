<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
/**
 * @implements PhpParserNodeMapperInterface<String_>
 */
final class StringNodeMapper implements \Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface
{
    public function getNodeType() : string
    {
        return \PhpParser\Node\Scalar\String_::class;
    }
    /**
     * @param String_ $node
     */
    public function mapToPHPStan(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\StringType();
    }
}
