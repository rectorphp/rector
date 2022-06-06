<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\PhpParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
/**
 * @implements PhpParserNodeMapperInterface<String_>
 */
final class StringNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType() : string
    {
        return String_::class;
    }
    /**
     * @param String_ $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        return new StringType();
    }
}
