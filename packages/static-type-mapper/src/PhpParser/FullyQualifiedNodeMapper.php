<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Type;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;

final class FullyQualifiedNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType(): string
    {
        return FullyQualified::class;
    }

    /**
     * @param FullyQualified $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        return new FullyQualifiedObjectType($node->toString());
    }
}
