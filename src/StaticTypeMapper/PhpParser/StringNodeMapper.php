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
